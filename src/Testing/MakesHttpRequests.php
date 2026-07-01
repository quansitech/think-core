<?php
namespace Testing;

use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

trait MakesHttpRequests
{
    /**
     * Additional headers for the request.
     *
     * @var array
     */
    protected $defaultHeaders = [];

    /**
     * Additional server variables for the request.
     *
     * @var array
     */
    protected $serverVariables = [];

    /**
     * Indicates whether redirects should be followed.
     *
     * @var bool
     */
    protected $followRedirects = false;

    /**
     * Define additional headers to be sent with the request.
     *
     * @param  array $headers
     * @return $this
     */
    public function withHeaders(array $headers)
    {
        $this->defaultHeaders = array_merge($this->defaultHeaders, $headers);

        return $this;
    }

    /**
     * Add a header to be sent with the request.
     *
     * @param  string $name
     * @param  string $value
     * @return $this
     */
    public function withHeader(string $name, string $value)
    {
        $this->defaultHeaders[$name] = $value;

        return $this;
    }

    /**
     * Flush all the configured headers.
     *
     * @return $this
     */
    public function flushHeaders()
    {
        $this->defaultHeaders = [];

        return $this;
    }

    /**
     * Define a set of server variables to be sent with the requests.
     *
     * @param  array  $server
     * @return $this
     */
    public function withServerVariables(array $server)
    {
        $this->serverVariables = $server;

        return $this;
    }

    /**
     * Set the referer header to simulate a previous request.
     *
     * @param  string  $url
     * @return $this
     */
    public function from(string $url)
    {
        return $this->withHeader('referer', $url);
    }

    /**
     * Visit the given URI with a GET request.
     *
     * @param  string  $uri
     * @param  array  $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function get($uri, array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('GET', $uri, [], [], [], $server);
    }

    /**
     * Visit the given URI with a GET request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function getJson($uri, array $headers = [])
    {
        return $this->json('GET', $uri, [], $headers);
    }

    /**
     * Visit the given URI with a POST request.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function post($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('POST', $uri, $data, [], [], $server);
    }

    /**
     * Visit the given URI with a POST request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function postJson($uri, array $data = [], array $headers = [])
    {
        return $this->json('POST', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a PUT request.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function put($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('PUT', $uri, $data, [], [], $server);
    }

    /**
     * Visit the given URI with a PUT request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function putJson($uri, array $data = [], array $headers = [])
    {
        return $this->json('PUT', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a PATCH request.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function patch($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('PATCH', $uri, $data, [], [], $server);
    }

    /**
     * Visit the given URI with a PATCH request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function patchJson($uri, array $data = [], array $headers = [])
    {
        return $this->json('PATCH', $uri, $data, $headers);
    }

    /**
     * Visit the given URI with a DELETE request.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function delete($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers);

        return $this->call('DELETE', $uri, $data, [], [], $server);
    }

    /**
     * Visit the given URI with a DELETE request, expecting a JSON response.
     *
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function deleteJson($uri, array $data = [], array $headers = [])
    {
        return $this->json('DELETE', $uri, $data, $headers);
    }

    /**
     * Call the given URI with a JSON request.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $data
     * @param  array  $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function json($method, $uri, array $data = [], array $headers = [])
    {
        $files = $this->extractFilesFromDataArray($data);

        $content = json_encode($data);

        $headers = array_merge([
            'CONTENT_LENGTH' => mb_strlen($content, '8bit'),
            'CONTENT_TYPE' => 'application/json',
            'HTTP_CONTENT_TYPE' => 'application/json',
            'Accept' => 'application/json',
        ], $headers);

        return $this->call(
            $method, $uri, [], [], $files, $this->transformHeadersToServerVars($headers), $content
        );
    }

    /**
     * Call the given URI and return the Response content.
     *
     * @param  string  $method
     * @param  string  $uri
     * @param  array  $parameters
     * @param  array  $cookies
     * @param  array  $files
     * @param  array  $server
     * @param  string  $content
     * @return  string response content
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $files = array_filter(array_merge($files, $this->extractFilesFromDataArray($parameters)));

        $symfonyRequest = SymfonyRequest::create(
            $this->prepareUrlForRequest($uri), $method, $parameters,
            $cookies, $files, array_replace($this->serverVariables, $server), $content
        );

        $this->packageTpRequest($symfonyRequest);

        return $this->runTpAsSanbox();
    }

    /**
     * 从响应内容解析 Inertia page 对象。
     *
     * v15 后台改用 Inertia.js + React，响应是含 `<div id="app" data-page="{...}">` 的
     * HTML（JSON 被 HTML 实体编码）。本辅助解析其中的 page 对象为数组，便于测试对
     * component / props.layoutProps.metaTitle 等结构断言，替代 v13 时代的 HTML 文案断言。
     *
     * @param  string $content 响应内容（$this->get(...) 的返回值）
     * @return array|null      Inertia page 对象；响应非 Inertia 页面时返回 null
     */
    public function inertiaPage(string $content): ?array
    {
        if (preg_match('/<div id="app"[^>]*data-page="([^"]*)"/', $content, $m)) {
            $json = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
            $page = json_decode($json, true);
            return is_array($page) ? $page : null;
        }
        return null;
    }

    protected function runTpAsSanbox(){
        $pipePath = "/tmp/test.pipe";

        if( file_exists( $pipePath ) ){
            unlink($pipePath);
        }

        if( !posix_mkfifo( $pipePath, 0666 ) ){
            exit('make pipe false!' . PHP_EOL);
        }

        $pid = pcntl_fork();

        if( $pid == 0 ){
            ob_clean();
            define("IS_CGI", 1);
            define("IS_CLI", false);
            try {
                require $this->projectPath() . '/tp.php';

                // 用 ob_get_clean() 取走并清空缓冲区，避免 exit() 时残留 ob 层级
                // 被 PHP 刷新到与父进程共享的 stdout，导致响应内容泄漏到命令行。
                $content = ob_get_clean();
            } catch (\Throwable $e) {
                // 子进程内 require tp.php 抛出的异常必须在此捕获，否则会冒泡到子进程
                // 继承的 PHPUnit 测试循环，导致子进程继续执行后续测试方法并嵌套 fork
                // （进而触发 “Constant IS_CGI already defined” 等串扰）。捕获后丢弃可能
                // 已写入 ob 的半截输出，把异常信息作为响应内容回传给父进程。
                ob_end_clean();
                $content = (method_exists($e, 'getMessage') ? $e->getMessage() : (string)$e);
            }

            $file = fopen( $pipePath, 'w' );
            fwrite( $file, $content);
            exit();
        }else{
            $file = fopen( $pipePath, 'r' );
            $content = fread( $file, 99999999 ) . PHP_EOL;
            pcntl_wait($status);
        }

        return $content;
    }

    protected function flushServerHeaders(){
        foreach($_SERVER as $name => $value){
            if (Str::startsWith($name, 'HTTP_')) unset($_SERVER[$name]);
        }
    }

    protected function packageTpRequest(SymfonyRequest $request){
        $this->flushServerHeaders();
        $_SERVER = array_merge($_SERVER, $request->server->all());
        $_GET = $request->query->all();
        $_POST = $request->request->all();

        $is_json_content_type = is_json_content_type();
        if($request->getMethod() === 'PUT' && !$is_json_content_type){
            $this->mockPhpInput($request->request->all());
        }
        if($is_json_content_type) {
            $this->mockPhpInput($request->getContent());
        }
        $_SERVER['PATH_INFO'] = parse_url($_SERVER['REQUEST_URI'])['path'];
        $_FILES = array_map(function($file){
            return [
                'name' => $file->getClientOriginalName(),
                'type' => $file->getMimeType(),
                'tmp_name' => $file->getPathname(),
                'error' => $file->getError(),
                'size' => $file->getSize()
            ];
        }, array_filter($request->files->all(), fn($file) => $file instanceof SymfonyUploadedFile));
    }

    protected function mockPhpInput($value): void
    {
        $fill_json = is_array($value) ? http_build_query($value) : $value;
        $stub = $this->createMock(TestingWall::class);
        $stub->method('file_get_contents')->willReturnMap([
            ['php://input', false, null, 0, null, $fill_json]
        ]);
        app()->instance(TestingWall::class, $stub);
    }

    /**
     * Turn the given URI into a fully qualified URL.
     *
     * @param  string  $uri
     * @return string
     */
    protected function prepareUrlForRequest($uri)
    {
        if (Str::startsWith($uri, '/')) {
            $uri = substr($uri, 1);
        }

        if (! Str::startsWith($uri, 'http')) {
            $uri = env('APP_URL').'/'.$uri;
        }

        return trim($uri, '/');
    }

    /**
     * Transform headers array to array of $_SERVER vars with HTTP_* format.
     *
     * @param  array  $headers
     * @return array
     */
    protected function transformHeadersToServerVars(array $headers)
    {
        return collect(array_merge($this->defaultHeaders, $headers))->mapWithKeys(function ($value, $name) {
            $name = strtr(strtoupper($name), '-', '_');

            return [$this->formatServerHeaderKey($name) => $value];
        })->all();
    }

    /**
     * Format the header name for the server array.
     *
     * @param  string  $name
     * @return string
     */
    protected function formatServerHeaderKey($name)
    {
        if (! Str::startsWith($name, 'HTTP_') && $name !== 'CONTENT_TYPE' && $name !== 'REMOTE_ADDR') {
            return 'HTTP_'.$name;
        }

        return $name;
    }

    /**
     * Extract the file uploads from the given data array.
     *
     * @param  array  $data
     * @return array
     */
    protected function extractFilesFromDataArray(&$data)
    {
        $files = [];

        foreach ($data as $key => $value) {
            if ($value instanceof SymfonyUploadedFile) {
                $files[$key] = $value;

                unset($data[$key]);
            }

            if (is_array($value)) {
                $files[$key] = $this->extractFilesFromDataArray($value);

                $data[$key] = $value;
            }
        }

        return $files;
    }

    public function loginSuperAdmin(){
        $this->ensureSessionStarted();
        session(C('USER_AUTH_KEY'), C('USER_AUTH_ADMINID'));
        session('ADMIN_LOGIN', true);
        session(C('ADMIN_AUTH_KEY'), true);
    }

    public function loginUser($uid){
        $this->ensureSessionStarted();
        session(C('USER_AUTH_KEY'), $uid);
        session('ADMIN_LOGIN', true);
    }

    /**
     * 确保 session 已在父进程真正启动。
     *
     * 父进程（PHPUnit 主进程）只加载了部分 ThinkPHP 配置，SESSION_AUTO_START 默认为 NULL，
     * 导致 session() 写值时 _session_start() 的守卫不通过，session 从未真正启动。
     * 这样 fork 出的子进程继承的是“未启动”状态，子进程首次 _session_start() 会因继承的
     * session_id 为空而 session_start() 生成新 id，并从空存储重载 $_SESSION，清空登录态。
     *
     * 显式开启 SESSION_AUTO_START，让父进程写值时正常触发 session_start()，子进程继承
     * “已启动”状态后会被 static 守卫跳过，直接读到继承的登录态。
     */
    private function ensureSessionStarted(){
        if (!C('SESSION_AUTO_START')) {
            C('SESSION_AUTO_START', true);
        }
    }

    public function getTpToken($request_uri, $is_ajax){
        list($tokenName,$tokenKey,$tokenValue) = getToken($request_uri, $is_ajax);
        return $tokenKey . '_' . $tokenValue;
    }

}
