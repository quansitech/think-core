<?php
namespace Qscmf\Core;

class QsListController extends QsController {

    /**
     * 透传 AuthStarter + BackendInitializer 给 QsController 构造，保持构造注入链完整。
     * 继承本类的业务控制器（无自定义构造）经容器 make 时，容器解析到本类构造签名，
     * 自动注入两协作者并透传给 QsController。
     */
    public function __construct(?AuthStarter $authStarter = null, ?BackendInitializer $backendInitializer = null)
    {
        parent::__construct($authStarter, $backendInitializer);
    }

    protected $_error;
    
    protected $_factory;
    
    protected $_view;
    
    protected function _getError(){
        return $this->_error;
    }

    protected function _forbid($id){
        $model = D($this->dbname);
        $r = $model->forbid($id);
        if($r === false){
            $this->_error = $model->getError();
        }
        return $r;
    }

    protected function _resume($id){
        $model = D($this->dbname);
        $r = $model->resume($id);
        if($r === false){
            $this->_error = $model->getError();
        }
        return $r;
    }

    protected function _del($id){
        $model = D($this->dbname);
        $r = $model->del($id);
        if($r === false){
            $this->_error = $model->getError();
        }
        return $r;
    }

}

