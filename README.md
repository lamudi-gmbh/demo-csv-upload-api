csvdemo
=======

CSV Upload API example

Setup
- You need to have PHP 5.4 minimum
- You need to run "php composer.phar install" from base_directory

- Download import templates from BOB for your country and use them for your files as base
  - You can use several attributesets in one run (file convention like importtemplate_land__20141105165221_694858.csv for land)
- There are 2 folders in data/csv/, one for new products (create) and other one for existing products (update).
  - To update products, put importtempate files there 
  - To create new products, put importtemplate files there
- Change the variable "bob.url" in "application/configs/application.ini", depending your current setup.
- Change the variable "bob.user" in "application/configs/application.ini", depending your current setup.
- Change the variable "bob.password" in "application/configs/application.ini", depending your current setup.
- Start the local PHP Webserver with "cd public; php -S localhost:8000"

Run
-
- Just go to http://localhost:8000/index/upload or http://localhost:8000/index/create to upload the .csv files located in the data folder.
