<?php

/**
 * Class IndexController
 * Example for the CSV upload API
 * Important: You NEED to enable LOAD DATA LOCAL INFILE in your MySQL configuration @see http://stackoverflow.com/questions/10762239/mysql-enable-load-data-local-infile
 */
class IndexController extends Zend_Controller_Action
{
    public function indexAction(){
        $this->view->message = 'Execute /index/upload or /index/create';
    }


    public function updateAction()
    {
        $result = $this->_uploadFilesFromDir(APPLICATION_PATH . '/../data/csv/update/*.csv', 'land', 'update');
        $this->_processResult($result);
    }

    public function createAction(){
        $result = $this->_uploadFilesFromDir(APPLICATION_PATH . '/../data/csv/create/*.csv', 'land', 'create');
        $this->_processResult($result);
    }

    /**
     * $httpClient is needed to pass parameters to XmlRpc client
     * @return Zend_XmlRpc_Client
     */
    protected function _getClient(){
        $url = 'http://intres.bob/import/csv/';
        set_time_limit(60);
        $httpClient = new Zend_Http_Client;

        $httpClient->setConfig(array('timeout' => '60'));

        return new Zend_XmlRpc_Client($url, $httpClient);
    }

    /**
     * Upload all the CSV files from a directory
     *
     * @param $dir Directory with the files
     * @param $attrSet Attribute Set choosen.
     * @param $mode create|update Mode to append the data
     *
     * @return array|mixed
     */
    protected function _uploadFilesFromDir($dir, $attrSet, $mode){
        $client = $this->_getClient();
        $files = glob($dir);

        foreach ($files as $file) {
            $data = file_get_contents($file);
            try {
                if (empty($data)) throw new Zend_XmlRpc_Client_FaultException('Trying to load an empty file', 999);
                return $client->call('csv.upload', array($attrSet, $mode, $data));
            } catch (Zend_XmlRpc_Client_FaultException $e) {
                return array('success' => false, 'details' => $e->getCode() . ' - ' . $e->getMessage());
            }
        }
        return array('success' => true, 'details' => 'No files in the provided directory');
    }

    /**
     * @param $result
     */
    protected function _processResult($result){
        if ($result['success']) {
            $this->view->message = "Import done! \n" . $details = (isset($result['details'])) ? $result['details'] : '';
        } else {
            $this->view->message = ($error = (isset($result['error'])) ? $result['error'] . ': ': '') . $result['details'];
        }
    }
}