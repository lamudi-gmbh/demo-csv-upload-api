<?php

class IndexControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
    protected $_updatePath = '/../data/csv/update/';
    protected $_createPath = '/../data/csv/create/';

    protected $_filesForUpdate;
    protected $_filesForCreate;

    protected $_mandatoryFieldsForUpdate = array(
        'sku'
    );
    protected $_mandatoryFieldsForCreate = array(
        'name',
        'item_contact_name',
        'item_contact_email',
        'is_agent',
        'listing_region',
        'listing_city',
        'listing_address',
        'price',
        'currency',
        'variation',
        'supplier'
    );

    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');

        $this->_filesForCreate = glob($this->_getCreatePath() . '*.csv');
        $this->_filesForUpdate = glob($this->_getUpdatePath() . '*.csv');

        parent::setUp();
    }

    public function testIndexControllerIsOk(){
        $this->dispatch('/');
        $this->assertController('index');
        $this->assertAction('index');
    }

    public function testUnknowControllerIsError(){
        $this->dispatch('/IamAnUnknowAction');
        $this->assertController('error');
    }

    public function testDirForUpdateFilesExists(){
        $this->assertFileExists($this->_getUpdatePath());
    }

    public function testDirForCreateFilesExists(){
        $this->assertFileExists($this->_getCreatePath());
    }

    public function testThereIsCsvFilesInUpdateDir(){
        $this->assertGreaterThan(0, count($this->_filesForUpdate));
    }

    public function testThereIsCsvFilesInCreateDir(){
        $this->assertGreaterThan(0, count($this->_filesForCreate));
    }

    public function testFilesForUpdateAreNotEmptyAndHasMandatoryFields(){
        $this->_checkFiles($this->_filesForUpdate, $this->_mandatoryFieldsForUpdate);
    }

    public function testFilesForCreateAreNotEmptyAndHasMandatoryFields(){
        $this->_checkFiles($this->_filesForCreate, $this->_mandatoryFieldsForCreate);
    }

    public function testUpdateIsNotError(){
        $this->dispatch('/index/update');
        $this->assertController('index');
        $this->assertAction('update');
    }

    public function testCreateIsNotError(){
        $this->dispatch('/index/create');
        $this->assertController('index');
        $this->assertAction('create');
    }






    protected function _getUpdatePath(){
        return APPLICATION_PATH . $this->_updatePath;
    }

    protected function _getCreatePath(){
        return APPLICATION_PATH . $this->_createPath;
    }

    protected function _checkFiles($files, $mandatoryFields){
        foreach ($files as $file){
            $data = file_get_contents($file);
            $csvData = str_getcsv($data, "\n");
            foreach($csvData as &$row) $row = str_getcsv($row, ";");

            $this->assertNotEmpty($data);
            foreach ($mandatoryFields as $field){
                $this->assertContains($field, $csvData[0]);
            }

        }
    }
}