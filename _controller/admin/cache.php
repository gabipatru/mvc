<?php
class controller_admin_cache extends ControllerAdminModel {
    function _prehook() {
        parent::_prehook();
        
        $Breadcrumbs = Breadcrumbs::getSingleton();
        $Breadcrumbs->Add(__('Cache'), href_admin('cache/list_cache'));
        
        mvc::assign('menu', 'cache');
    }
    
    function list_cache() {
        
    }
    
    function memcached() {
        $FV = new FormValidation(array(
            'rules' => array(
                'memcached_key' => 'required'
            ),
            'messages' => array(
                'memcached_key' => __('Please specify the memcached key you want to delete')
            )
        ));
        
        $memcache = Mcache::getSingleton();
        $aMemcacheStats = current($memcache->getStats());
        Mcache::prettyStats($aMemcacheStats);
        
        $Breadcrumbs = Breadcrumbs::getSingleton();
        $Breadcrumbs->Add(__('Memcached'), MVC_ACTION_URL);

        mvc::assign_by_ref('aMemcacheStats', $aMemcacheStats);
        mvc::assign('FV', $FV);
    }
    
    function flush_memcached() {
        $FV = new FormValidation(array(
            'rules' => array(
                'memcached_key' => 'required'
            ),
            'messages' => array(
                'memcached_key' => __('Please specify the memcached key you want to delete')
            )
        ));
        
        $validate = $FV->validate();
        if (isPOST()) {
            try {
                if (!$validate) {
                    throw new Exception(__('Please make sure you filled all the required fields'));
                }
                if (!securityCheckToken(filter_post('token', 'string'))) {
                    throw new Exception(__('The page delay was too long'));
                }
                
                $key = filter_post('memcached_key', 'string');
                
                $memcache = Mcache::getSingleton();
                $memcache->delete($key);
                
                message_set('Key '. $key .' deleted !');
            }
            catch (Exception $e) {
                message_set_error($e->getMessage());
            }
        }
        
        http_redir(href_admin('cache/memcached'));
    }
    
    function flush_all_memcached() {
        if (isPOST()) {
            try {
                if (!securityCheckToken(filter_post('token', 'string'))) {
                    throw new Exception(__('The page delay was too long'));
                }
                
                $memcache = Mcache::getSingleton();
                $memcache->flush();
                
                message_set(__('Memcached keys flushed'));
            }
            catch (Exception $e) {
                message_set_error($e->getMessage());
            }
        }
        
        http_redir(href_admin('cache/memcached'));
    }
}