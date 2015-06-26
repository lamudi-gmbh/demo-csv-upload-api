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
    protected $bobUser;
    protected $bobPassword;

    public function __construct(
        Zend_Controller_Request_Abstract $request,
        Zend_Controller_Response_Abstract $response, array $invokeArgs = array()
    ) {
        $config = $invokeArgs['bootstrap']->getApplication()->getOption('bob');
        $this->bobUrl = $config['url'];
        $this->bobUser = $config['user'];
        $this->bobPassword = $config['password'];
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
        $this->_processResult($result, 'update');
    }

    /**
     * create Action
     */
    public function createAction()
    {
        $result = $this->_uploadFilesFromDir(
            APPLICATION_PATH . '/../data/csv/create/*.csv', 'create'
        );
        $this->_processResult($result, 'create');
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
        $httpClient->setAuth($this->bobUser, $this->bobPassword);
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
                $lines = count(file($file)) - 1;
                if (empty($data)) {
                    // if file is empty, jump over this file
                    $return[] = array(
                        'success'   => false,
                        'details'   => $fileBasename . ' is empty.',
                        'file'      => $fileBasename);
                    continue;
                }
                // log time
                $timeStart = microtime(true);

                // call csv api
                $response = $client->call(
                    'csv.upload', array($attrSet, $mode, $data)
                );

                // calculate elapsed time
                $timeEnd = microtime(true);
                $time = number_format($timeEnd - $timeStart, 2) . 's';
                // add filename to response
                $response['file'] = $fileBasename;
                $response['attribute_set'] = $attrSet;
                $response['time'] = $time;
                $response['lines_send'] = $lines;
                // add response to return array
                $return[] = $response;

            } catch (Zend_XmlRpc_Client_FaultException $e) {
                $timeEnd = microtime(true);
                $time = number_format($timeEnd - $timeStart, 2) . 's';
                // if exception, set error message
                $return[] = array(
                    'success'   => false,
                    'details'   => $e->getCode() . ' - ' . $e->getMessage(),
                    'file'      => $fileBasename,
                    'attribute_set' => $attrSet,
                    'time'      => $time,
                    'lines_send'     => $lines
                );
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
     * @param array $result
     * @param string $type
     */
    protected function _processResult(array $resultArray, $type = '')
    {
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json');
        $json = json_encode($resultArray);
        $this->view->json = $json;
        $logFileName = $type . '_' . time() . '.log';
        //log to file
        file_put_contents(APPLICATION_PATH . '/../logs/' . $logFileName, $json);
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
        if (count($explode) >= 3) {
            return $explode[1]; //land or any other attrtype
        }
        return array('success' => false,
                     'details' => 'Filename is not in the needed format like '
                         . 'importtemplate_ATTRSET__TIMESTAMP_ID.csv !'
                         . 'Example is importtemplate_land__20140917092219_877300.csv');
    }

}