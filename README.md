This project focus on the parsing and filtering of the spread sheet data provided.
The spreadsheet is provided to the UI or the client using a REST API. The API would
parse the spread sheet and then provide the result as a JSON response to the consumer
of the API.

Requirements for this project
-----------------------------
Download symfony binary using command as shown below:
```
wget https://get.symfony.com/cli/installer -O - | bash
```
I have used symfony 5 for this project.

The version used by me for testing is : `Symfony CLI version v4.12.2`

How is this project created?
----------------------------
This project is created using command shown below:
```
composer create-project symfony/website-skeleton serverfilter
```

A consumer of this REST API shouldn't be worried about this, becasue all the necessary packages are available
as part of this project.

Server
------
The server can be run by using `symfony server:start` from the root of the project.

Spreadsheet column
------------------
As of now the expected columns in the spreadsheet:
```
column A - `Model`
column B - `RAM`
column C - `HDD`
column D - `Location`
column E - `Price`
```
This is the expectation from the file, hence do not change the format.


Memory management for parsing spread sheet
------------------------------------------
Only 200 rows of spread sheet is loaded to the memory at a time. This means, chunking is implemented.
Instead of loading entire spreadsheet to the memory, only a chunk is loaded and then data is processed
based on the inputs provided. The process is continued to get the final desired output, if available.

How to use this REST API?
-------------------------
This API accepts the following arguments as mandatory:
```
`file` - The absolute path of the spread sheet file.
```

Optional arguments are:
```
`storage`  - A range of storage is accepted. For example the value can be of range 120GB-1TB. This means
             the spread sheet rows which has range between 120GB to 1TB (both 120GB and 1TB are inclusive).
             Remember the separator between first and last value is a minus sign(-).
`ram`      - A range of ram values is accepted. For example the values can be provided as `1GB,2GB,4GB,12GB,16GB`
             The spread sheet rows which matches with values 1GB or 2GB or 4GB or 12GB or 16GB is selected. Say
             if the ram value is 3GB it will not be selected.
`hdisk`    - User can provide value as string. Any valid string can be provided. Meaning user can provide `SAS` or
             `SATA` or `SATA2` or `SSD`. 
`location` - User can provide location as string. Any valid string can be provided.
`offset`   - The row position from where the data will be fetched from.
`limit`    - The maximum allowed data to be fetched.
```

Inorder to access the API using client like curl, an API key should be provided in the header. I have added this API key as `filter-api-key`
with value `27edd872006136539259345e9555f3c99495ebf0`. This value can be changed at `config/services.yaml` under
`parameters` section. But do not change the keys, i.e, both `filter-api-key` and `file-directory`.
While accessing the UI from browser, this key is set internally.
The clean approach will be to write a separate authentication service and then handle it. But to make this API simple for now
it relies on API key.

Example:
```
    curl http://localhost:8000/filterResult\?location\=AmsterdamAMS-01\&storage\=120GB-16TB\&hdisk\=SATA2\&ram\=16GB,32GB,48GB\&limit\=100\&offset\=1\&file\=\/home\/sujith\/leaseweb_filter_exercise\/serverfilters\/servers_filters_assignment.xlsx -H "filter-api-key:27edd872006136539259345e9555f3c99495ebf0"
```

If the `offset` and `limit` is not specified, the default value is set to `1` for offset and `30` for `limit`. This means
starting from first offset the API would provide 30 results. The `rowIndex` shared in the JSON output will provide the
next data available at the offset. User can now reset the offset to the value of `rowIndex`.

Sample JSON output:
-------------------
```
{
   "0" : {
      "Model" : "IBM X3650M42x Intel Xeon E5-2620",
      "HDD" : "2x1TBSATA2",
      "RAM" : "32GBDDR3",
      "Location" : "DallasDAL-10",
      "Price" : "$220.99"
   },
   "1" : {
      "Location" : "DallasDAL-10",
      "Price" : "$225.99",
      "Model" : "HP DL380pG82x Intel Xeon E5-2620",
      "RAM" : "32GBDDR3",
      "HDD" : "2x1TBSATA2"
   },
   "2" : {
      "rowIndex" : 354
   },
   "3" : {
      "startrow" : 201
   },   
}
```
Tests
-----
Tests are created under tests folder. The tests are written in PHPUnit.
After the repo is cloned kindly run the commands below:
```
./bin/phpunit
```
from the root of the folder to execute the tests.
Initially packages will be fetched and installed. And subsequently while adding tests
user could just run `./bin/phpunit`.
