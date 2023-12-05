<?php
namespace Qscmf\Lib\Tp3Resque\Resque;


use Qscmf\Lib\Tp3Resque\Resque;
use Qscmf\Lib\Tp3Resque\Resque\Job\Status;
use InvalidArgumentException;
use Exception;
use Qscmf\Lib\Tp3Resque\Resque\Job\DontPerform;

/**
 * Resque job.
 *
 * @package		Resque/Job
 * @author		Chris Boulton <chris@bigcommerce.com>
 * @license		http://www.opensource.org/licenses/mit-license.php
 */
class Job
{
	/**
	 * @var string The name of the queue that this job belongs to.
	 */
	public $queue;

	/**
	 * @var Resque_Worker Instance of the Resque worker running this job.
	 */
	public $worker;

	/**
	 * @var object Object containing details of the job.
	 */
	public $payload;

	/**
	 * @var object Instance of the class performing work for this job.
	 */
	private $instance;

	/**
	 * Instantiate a new instance of a job.
	 *
	 * @param string $queue The queue that the job belongs to.
	 * @param array $payload array containing details of the job.
	 */
	public function __construct($queue, $payload)
	{
		$this->queue = $queue;
		$this->payload = $payload;
	}

	/**
	 * Create a new job and save it to the specified queue.
	 *
	 * @param string $queue The name of the queue to place the job in.
	 * @param string $class The name of the class that contains the code to execute the job.
	 * @param array $args Any optional arguments that should be passed when the job is executed.
	 * @param boolean $monitor Set to true to be able to monitor the status of a job.
	 *
	 * @return string
	 */
	public static function create($queue, $class, $args = null, $monitor = false)
	{
		if($args !== null && !is_array($args)) {
			throw new \InvalidArgumentException(
				'Supplied $args must be an array.'
			);
		}
		$id = md5(uniqid('', true));
		Resque::push($queue, array(
			'class'	=> $class,
			'args'	=> array($args),
			'id'	=> $id,
                        'repeat_times' => 0,
		));

		if($monitor) {
			Status::create($id);
		}

		return $id;
	}

	public static function createBySchedule($schedule_id, $queue, $class, $args = null, $monitor = false)
	{
		if($args !== null && !is_array($args)) {
			throw new InvalidArgumentException(
				'Supplied $args must be an array.'
			);
		}

		$job_id = md5(uniqid('', true));

		$pay_load = json_encode(array(
			'class'	=> $class,
			'args'	=> array($args),
			'id'	=> $job_id,
			'repeat_times' => 0,
		));

		$lua = <<<lua
local namespace = KEYS[2]
local queue = KEYS[1]
local schedule_id = ARGV[1]
local schedule_h_k = namespace..queue.."_schedule"
local schedule_zset_k = namespace..queue.."_schedule_sort"
local queue_s_k = namespace.."queues"
local queue_l_k = namespace.."queue:"..queue
local job_payload = ARGV[2]
local del_num = redis.call('hdel', schedule_h_k, schedule_id)
if del_num == 1 then
	redis.call('zrem', schedule_zset_k, schedule_id)
	redis.call('sadd', queue_s_k, queue)
	return redis.call('rpush', queue_l_k,job_payload)
else
	return 0
end
lua;
		$redis = Resque::redis();
		$r = $redis->eval($lua, 2, $queue, $redis::$defaultNamespace, $schedule_id, $pay_load);
		if($r >=1){
			if($monitor) {
				Status::create($job_id);
			}

			return $job_id;
		}
		else{
			return false;
		}

	}

	/**
	 * Find the next available job from the specified queue and return an
	 * instance of Resque_Job for it.
	 *
	 * @param string $queue The name of the queue to check for a job in.
	 * @return null|object Null when there aren't any waiting jobs, instance of Resque_Job when a job was found.
	 */
	public static function reserve($queue)
	{
		$payload = Resque::pop($queue);
		if(!is_array($payload)) {
			return false;
		}
		return new Job($queue, $payload);
	}

	/**
	 * Update the status of the current job.
	 *
	 * @param int $status Status constant from Resque_Job_Status indicating the current status of a job.
	 */
	public function updateStatus($status)
	{
		if(empty($this->payload['id'])) {
			return;
		}
		$statusInstance = new Status($this->payload['id']);
		$statusInstance->update($status);
	}

	/**
	 * Return the status of the current job.
	 *
	 * @return int The status of the job as one of the Resque_Job_Status constants.
	 */
	public function getStatus()
	{
		$status = new Status($this->payload['id']);
		return $status->get();
	}

	/**
	 * Get the arguments supplied to this job.
	 *
	 * @return array Array of arguments.
	 */
	public function getArguments()
	{
		if (!isset($this->payload['args'])) {
			return array();
		}
		return $this->payload['args'][0];
	}

	/**
	 * Get the instantiated object for this job that will be performing work.
	 *
	 * @return object Instance of the object that this job belongs to.
	 */
	public function getInstance()
	{
		if (!is_null($this->instance)) {
			return $this->instance;
		}

		if(!class_exists($this->payload['class'])) {
			throw new Exception(
				'Could not find job class ' . $this->payload['class'] . '.'
			);
		}

		if(!method_exists($this->payload['class'], 'perform')) {
			throw new Exception(
				'Job class ' . $this->payload['class'] . ' does not contain a perform method.'
			);
		}

		$this->instance = new $this->payload['class']();
		$this->instance->job = $this;
		$this->instance->args = $this->getArguments();
		$this->instance->queue = $this->queue;
		return $this->instance;
	}

	/**
	 * Actually execute a job by calling the perform method on the class
	 * associated with the job with the supplied arguments.
	 *
	 * @return bool
	 * @throws Resque_Exception When the job's class could not be found or it does not contain a perform method.
	 */
	public function perform()
	{
		$instance = $this->getInstance();
		try {
			Event::trigger('beforePerform', ['job'=>$this]);

			if(method_exists($instance, 'setUp')) {
				$instance->setUp();
			}

			$instance->perform();

			if(method_exists($instance, 'tearDown')) {
				$instance->tearDown();
			}

			Event::trigger('afterPerform', ['job'=>$this]);
		}
		// beforePerform/setUp have said don't perform this job. Return.
		catch(DontPerform $e) {
			return false;
		}

		return true;
	}
        
        public function repeat($exception){
            $this->payload['repeat_times'] = $this->payload['repeat_times'] + 1;
            
            if($this->payload['repeat_times'] > C('RESQUE_JOB_REPEAT_TIMES')){
                $this->fail($exception);
            }
            else{
                $this->reEnqueue();
            }
            
        }
        
        public function reEnqueue(){
            Resque::push($this->queue, $this->payload);
                
            $status = new Status($this->payload['id']);
            if($status->isTracking()) {
                Status::create($this->payload['id']);
            }
        }

	/**
	 * Mark the current job as having failed.
	 *
	 * @param $exception
	 */
	public function fail($exception)
	{
		Event::trigger('onFailure', array(
			'exception' => $exception,
			'job' => $this,
		));
                
		$this->updateStatus(Status::STATUS_FAILED);
                \Think\Log::write("job:" . $this);
                \Think\Log::write($exception->getMessage());
		Failure::create(
			$this->payload,
			$exception,
			$this->worker,
			$this->queue
		);
		Stat::incr('failed');
		Stat::incr('failed:' . $this->worker);
	}

	/**
	 * Re-queue the current job.
	 * @return string
	 */
	public function recreate()
	{
		$status = new Status($this->payload['id']);
		$monitor = false;
		if($status->isTracking()) {
			$monitor = true;
		}

		return self::create($this->queue, $this->payload['class'], $this->payload['args'], $monitor);
	}

	/**
	 * Generate a string representation used to describe the current job.
	 *
	 * @return string The string representation of the job.
	 */
	public function __toString()
	{
		$name = array(
			'Job{' . $this->queue .'}'
		);
		if(!empty($this->payload['id'])) {
			$name[] = 'ID: ' . $this->payload['id'];
		}
		$name[] = $this->payload['class'];
		if(!empty($this->payload['args'])) {
			$name[] = json_encode($this->payload['args']);
		}
		return '(' . implode(' | ', $name) . ')';
	}
}
?>
