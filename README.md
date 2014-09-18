csvdemo
=======

CSV Upload API example

Setup
-
- You need to enable LOAD DATA LOCAL INFILE in your MySQL configuration <br>
--- Add in your MySQL config file (usually /etc/mysql/my.cnf) these lines:
```
[mysqld]
local-infile
[mysql]
local-infile
```
- There is 2 folders in data/csv/, one for new products (create) and other one for existing products (update).<br>
  - To update a product. is needed a .csv file with the sku of the product to update
  - To create a new product, is needed a .csv file with the following fields:
    - name, item_contact_name, item_contact_email, is_agent, listing_region, listing_city, listing_address, price, currency, variation, supplier
