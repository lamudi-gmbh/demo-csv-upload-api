<?php
set_time_limit(600);

/**
 * Class IndexController
 * Example for the CSV upload API
 */
class IndexController extends Zend_Controller_Action
{

    /**
     * constant for line ending (PHP_EOL will not work)
     */
    const EOL = '<br/>';

    protected $bobUrl;

    public function __construct(
        Zend_Controller_Request_Abstract $request,
        Zend_Controller_Response_Abstract $response, array $invokeArgs = array()
    ) {
        $config = $invokeArgs['bootstrap']->getApplication()->getOption('bob');
        $this->bobUrl = $config['url'];
        parent::__construct($request, $response, $invokeArgs);
    }

    /**
     * index action
     */
    public function indexAction()
    {
        $this->view->message = 'Execute /index/upload or /index/create';
    }

    /**
     * update action
     */
    public function updateAction()
    {
        $result = $this->_uploadFilesFromDir(
            APPLICATION_PATH . '/../data/csv/update/*.csv', 'update'
        );
        $this->_processResult($result);
    }

    /**
     * create Action
     */
    public function createAction()
    {
        $result = $this->_uploadFilesFromDir(
            APPLICATION_PATH . '/../data/csv/create/*.csv', 'create'
        );
        $this->_processResult($result);
    }

    /**
     * $httpClient is needed to pass parameters to XmlRpc client
     *
     * @return Zend_XmlRpc_Client
     */
    protected function _getClient()
    {
        $httpClient = new Zend_Http_Client;
        $httpClient->setConfig(array('timeout' => '600'));
        return new Zend_XmlRpc_Client($this->bobUrl, $httpClient);
    }

    /**
     * Upload all the CSV files from a directory
     *
     * @param $dir     Directory with the files
     * @param $mode    create|update Mode to append the data
     *
     * @return array|mixed
     */
    protected function _uploadFilesFromDir($dir, $mode)
    {
        $client = $this->_getClient();
        $files = glob($dir);
        $i = 0;
        $return = array();

        foreach ($files as $file) {
            $i++;
            $file = realpath($file);
            $fileBasename = basename($file);
            $attrSet = $this->findAttrSetFromFileName($file);

            // if we get back an array for attribute set, we have an error
            // jump over this file
            if (is_array($attrSet)) {
                $attrSet['file'] = $fileBasename;
                $return[] = $attrSet;
                continue;
            }

            try {
                // read data from file and try to upload
                $data = file_get_contents($file);

                if (empty($data)) {
                    // if file is empty, jump over this file
                    $return[] = array('success' => false,
                                      'details' => $fileBasename . ' is empty.',
                                      'file'    => $fileBasename);
                    continue;
                }
                // call csv api
                $response = $client->call(
                    'csv.upload', array($attrSet, $mode, $data)
                );
                // add filename to response
                $response['file'] = $fileBasename;
                // add response to return array
                $return[] = $response;

            } catch (Zend_XmlRpc_Client_FaultException $e) {
                // if exception, set error message
                $return[] = array('success' => false,
                                  'details' => $e->getCode() . ' - '
                                      . $e->getMessage(),
                                  'file'    => $fileBasename);
            }
        }

        //check that we have processed some files
        if ($i == 0) {
            return array('success' => false,
                         'details' => 'No files in the provided directory');
        }
        // return the array with the process details
        return $return;
    }

    /**
     * process the result
     *
     * @param $result
     */
    protected function _processResult(array $resultArray)
    {
        //loop over result array
        foreach ($resultArray as $result) {

            if ($result['success']) {
                if (isset($result['details'])) {
                    $details = $result['details'];
                } else {
                    $details = '';
                }
                // create view message
                $this->view->message
                    .= "Import done for file " . $result['file'] . $details;
            } else {
                $error
                    = "Import Error for file " . $result['file'] . static::EOL
                    . $result['error'] . ' - ' . $result['details'];
                // create view message
                $this->view->message .= $error;
            }
        }
    }

    /**
     * @param $file
     *
     * @throws Exception
     */
    protected function findAttrSetFromFileName($file)
    {
        //importtemplate_land__20140917092219_877300.csv
        /** @var array $explode */
        $explode = explode('_', basename($file));
        if (count($explode) == 5) {
            return $explode[1]; //land or any other attrtype
        }
        return array('success' => false,
                     'details' => 'Filename is not in the needed format like '
                         . 'importtemplate_ATTRSET__TIMESTAMP_ID.csv !'
                         . 'Example is importtemplate_land__20140917092219_877300.csv');
    }

}