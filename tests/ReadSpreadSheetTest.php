<?php
/**
 * @author Sujith Haridasan <sujith.h@gmail.com>
 *
 * @copyright Copyright (c) 2020
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 */

namespace App\Tests;


use App\Service\ReadSpreadSheet;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ReadSpreadSheetTest extends TestCase
{
    private $logger;
    private $readspsheet;
    const FILENAME = "servers_filters.xlsx";
    protected function setUp()
    {
        parent::setUp();
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->readspsheet = new ReadSpreadSheet($this->logger);
    }

    /**
     * This test does not pas any filter params to the API
     * The idea here is to fetch the first chunk of the file.
     * This also proves that complete file will not be loaded into memory and
     * hence it prevents memory hog.
     *
     */
    public function testReadFileCompletely() {
        $absPathOfFile = __DIR__ . '/'. self::FILENAME;
        $params = [
            'storage' => null,
            'ram' => null,
            'hdisk' => null,
            'location' => null,
            'limit' => null,
            'offset' => null,
        ];
        $data = $this->readspsheet->readFile($absPathOfFile, $params);
        $dataFetched = \json_decode($data, true);
        $expectedResult = [
            [
                'Model' => 'Dell R210Intel Xeon X3440',
                'RAM' => '16GBDDR3',
                'HDD' => '2x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€49.99',
            ],
            [
                'Model' => 'HP DL180G62x Intel Xeon E5620',
                'RAM' => '32GBDDR3',
                'HDD' => '8x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€119.00',
            ],
            [
                'Model' => 'HP DL380eG82x Intel Xeon E5-2420',
                'RAM' => '32GBDDR3',
                'HDD' => '8x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€131.99',
            ],
            [
                'Model' => 'RH2288v32x Intel Xeon E5-2650V4',
                'RAM' => '128GBDDR4',
                'HDD' => '4x480GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€227.99',
            ],
            [
                'Model' => 'RH2288v32x Intel Xeon E5-2620v4',
                'RAM' => '64GBDDR4',
                'HDD' => '4x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€161.99',
            ],
            [
                'Model' => 'Dell R210-IIIntel Xeon E3-1230v2',
                'RAM' => '16GBDDR3',
                'HDD' => '2x2TBSATA2',
                'Location' => 'FrankfurtABC-01',
                'Price' => '€72.99',
            ],
            [
                'Model' => 'HP DL380pG82x Intel Xeon E5-2650',
                'RAM' => '64GBDDR3',
                'HDD' => '8x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€179.99',
            ],
            [
                'Model' => 'IBM X36302x Intel Xeon E5620',
                'RAM' => '32GBDDR3',
                'HDD' => '8x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€106.99',
            ],
            [
                'Model' => 'HP DL120G7Intel G850',
                'RAM' => '4GBDDR3',
                'HDD' => '4x1TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€39.99',
            ],
            [
                'Model' => 'Dell R730XD2x Intel Xeon E5-2667v4',
                'RAM' => '128GBDDR4',
                'HDD' => '2x120GBSSD',
                'Location' => 'SingaporeSIN-01',
                'Price' => '€364.99',
            ],
            [
                'Model' => 'Dell R730XD2x Intel Xeon E5-2670v3',
                'RAM' => '128GBDDR4',
                'HDD' => '2x120GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€364.99',
            ],
            [
                'Model' => 'Dell R730XD2x Intel Xeon E5-2650v3',
                'RAM' => '128GBDDR4',
                'HDD' => '4x480GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€279.99',
            ],
            [
                'Model' => 'Dell R730XD2x Intel Xeon E5-2650v4',
                'RAM' => '128GBDDR4',
                'HDD' => '4x480GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€286.99',
            ],
            ['rowIndex' => 14],
            ['startrow' => 202],
        ];
        $this->assertEquals($expectedResult, $dataFetched);
    }

    /**
     * Test by passing only RAM filter to the API.
     * The return from the API call will have 2 more
     * indices in the array. So the exact content is
     * the count(dataFetched) - 2.
     */
    public function testReadFileWithRAMFilter() {
        $absPathOfFile = __DIR__ . '/'. self::FILENAME;
        $params = [
            'storage' => null,
            'ram' => '64GB',
            'hdisk' => null,
            'location' => null,
            'limit' => 5,
            'offset' => 1,
        ];
        $data = $this->readspsheet->readFile($absPathOfFile, $params);
        $dataFetched = \json_decode($data, true);
        $expectedResult = [
            [
                'Model' => 'RH2288v32x Intel Xeon E5-2620v4',
                'RAM' => '64GBDDR4',
                'HDD' => '4x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€161.99',
            ],
            [
                'Model' => 'HP DL380pG82x Intel Xeon E5-2650',
                'RAM' => '64GBDDR3',
                'HDD' => '8x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€179.99',
            ],
            ['rowIndex' => 14],
            ['startrow' => 201]
        ];
        $this->assertEquals($expectedResult, $dataFetched);
    }

    /**
     * Test by passing only storage filter to the API.
     * The return from the API call will have 2 more
     * indices in the array. So the exact content is
     * the count(dataFetched) - 2.
     */
    public function testReadFileWithStorageFilter() {
        $absPathOfFile = __DIR__ . '/'. self::FILENAME;
        $params = [
            'storage' => '4TB',
            'ram' => null,
            'hdisk' => null,
            'location' => null,
            'limit' => 5,
            'offset' => 1,
        ];
        $data = $this->readspsheet->readFile($absPathOfFile, $params);
        $expectedResult = [
            [
                'Model' => 'Dell R210Intel Xeon X3440',
                'RAM' => '16GBDDR3',
                'HDD' => '2x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€49.99',
            ],
            [
                'Model' => 'Dell R210-IIIntel Xeon E3-1230v2',
                'RAM' => '16GBDDR3',
                'HDD' => '2x2TBSATA2',
                'Location' => 'FrankfurtABC-01',
                'Price' => '€72.99',
            ],
            [
                'Model' => 'HP DL120G7Intel G850',
                'RAM' => '4GBDDR3',
                'HDD' => '4x1TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€39.99',
            ],
            ['rowIndex' => 14],
            ['startrow' => 201],
        ];
        $dataFetched = \json_decode($data, true);
        $this->assertEquals($expectedResult, $dataFetched);
    }

    /**
     * Test by passing only harddisk filter to the API.
     * The return from the API call will have 2 more
     * indices in the array. So the exact content is
     * the count(dataFetched) - 2.
     */
    public function testReadFileHarddiskFilter() {
        $absPathOfFile = __DIR__ . '/'. self::FILENAME;
        $params = [
            'storage' => null,
            'ram' => null,
            'hdisk' => 'SSD',
            'location' => null,
            'limit' => 10,
            'offset' => 1,
        ];
        $data = $this->readspsheet->readFile($absPathOfFile, $params);
        $expectedResult = [
            [
                'Model' => 'RH2288v32x Intel Xeon E5-2650V4',
                'RAM' => '128GBDDR4',
                'HDD' => '4x480GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€227.99',
            ],
            [
                'Model' => 'Dell R730XD2x Intel Xeon E5-2667v4',
                'RAM' => '128GBDDR4',
                'HDD' => '2x120GBSSD',
                'Location' => 'SingaporeSIN-01',
                'Price' => '€364.99',
            ],
            [
                'Model' => 'Dell R730XD2x Intel Xeon E5-2670v3',
                'RAM' => '128GBDDR4',
                'HDD' => '2x120GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€364.99',
            ],
            [
                'Model' => 'Dell R730XD2x Intel Xeon E5-2650v3',
                'RAM' => '128GBDDR4',
                'HDD' => '4x480GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€279.99',
            ],
            [
                'Model' => 'Dell R730XD2x Intel Xeon E5-2650v4',
                'RAM' => '128GBDDR4',
                'HDD' => '4x480GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€286.99',
            ],
            ['rowIndex' => 14],
            ['startrow' => 201],
        ];
        $dataFetched = \json_decode($data, true);
        $this->assertEquals($expectedResult, $dataFetched);
    }

    /**
     * Test by passing only location filter to the API.
     * The return from the API call will have 2 more
     * indices in the array. So the exact content is
     * the count(dataFetched) - 2.
     */
    public function testReadFileLocationFilter() {
        $absPathOfFile = __DIR__ . '/'. self::FILENAME;
        $params = [
            'storage' => null,
            'ram' => null,
            'hdisk' => null,
            'location' => 'AmsterdamAMS-01',
            'limit' => 15,
            'offset' => 1,
        ];
        $data = $this->readspsheet->readFile($absPathOfFile, $params);
        $dataFetched = \json_decode($data, true);
        $expectedResult = [
            [
                'Model' => 'Dell R210Intel Xeon X3440',
                'RAM' => '16GBDDR3',
                'HDD' => '2x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€49.99',
            ],
            [
                'Model' => 'HP DL180G62x Intel Xeon E5620',
                'RAM' => '32GBDDR3',
                'HDD' => '8x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€119.00',
            ],
            [
                'Model' => 'HP DL380eG82x Intel Xeon E5-2420',
                'RAM' => '32GBDDR3',
                'HDD' => '8x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€131.99',
            ],
            [
                'Model' => 'RH2288v32x Intel Xeon E5-2650V4',
                'RAM' => '128GBDDR4',
                'HDD' => '4x480GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€227.99',
            ],
            [
                'Model' => 'RH2288v32x Intel Xeon E5-2620v4',
                'RAM' => '64GBDDR4',
                'HDD' => '4x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€161.99',
            ],
            [
                'Model' => 'HP DL380pG82x Intel Xeon E5-2650',
                'RAM' => '64GBDDR3',
                'HDD' => '8x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€179.99',
            ],
            [
                'Model' => 'IBM X36302x Intel Xeon E5620',
                'RAM' => '32GBDDR3',
                'HDD' => '8x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€106.99',
            ],
            [
                'Model' => 'HP DL120G7Intel G850',
                'RAM' => '4GBDDR3',
                'HDD' => '4x1TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€39.99',
            ],
            [
                'Model' => 'Dell R730XD2x Intel Xeon E5-2670v3',
                'RAM' => '128GBDDR4',
                'HDD' => '2x120GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€364.99',
            ],
            [
                'Model' => 'Dell R730XD2x Intel Xeon E5-2650v3',
                'RAM' => '128GBDDR4',
                'HDD' => '4x480GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€279.99',
            ],
            [
                'Model' => 'Dell R730XD2x Intel Xeon E5-2650v4',
                'RAM' => '128GBDDR4',
                'HDD' => '4x480GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€286.99',
            ],
            ['rowIndex' => 14],
            ['startrow' => 201],
        ];
        $this->assertEquals($expectedResult, $dataFetched);
    }

    /**
     * Test by passing only RAM filter, storage filter, harddisk filter and location filter to the API.
     * The return from the API call will have 2 more indices in the array. So the exact content is
     * the count(dataFetched) - 2.
     */
    public function testReadFileAllFilter() {
        $absPathOfFile = __DIR__ . '/'. self::FILENAME;
        $params = [
            'storage' => '240GB',
            'ram' => '128GB',
            'hdisk' => 'SSD',
            'location' => 'AmsterdamAMS-01',
            'limit' => 8,
            'offset' => 1,
        ];
        $data = $this->readspsheet->readFile($absPathOfFile, $params);
        $dataFetched = \json_decode($data, true);
        $expectedResult = [
            [
                'Model' => 'Dell R730XD2x Intel Xeon E5-2670v3',
                'RAM' => '128GBDDR4',
                'HDD' => '2x120GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€364.99',
            ],
            ['rowIndex' => 14],
            ['startrow' => 201],
        ];
        $this->assertEquals($expectedResult, $dataFetched);
    }

    /**
     * Test the API with range of storage options provided.
     */
    public function testReadFileRangeOfStorage() {
        $absPathOfFile = __DIR__ . '/'. self::FILENAME;
        $params = [
            'storage' => '1TB-4TB',
            'ram' => null,
            'hdisk' => null,
            'location' => null,
            'limit' => 10,
            'offset' => 1,
        ];
        $data = $this->readspsheet->readFile($absPathOfFile, $params);
        $dataFetched = \json_decode($data, true);
        $expectedResult = [
            [
                'Model' => 'Dell R210Intel Xeon X3440',
                'RAM' => '16GBDDR3',
                'HDD' => '2x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€49.99',
            ],
            [
                'Model' => 'RH2288v32x Intel Xeon E5-2650V4',
                'RAM' => '128GBDDR4',
                'HDD' => '4x480GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€227.99',
            ],
            [
                'Model' => 'Dell R210-IIIntel Xeon E3-1230v2',
                'RAM' => '16GBDDR3',
                'HDD' => '2x2TBSATA2',
                'Location' => 'FrankfurtABC-01',
                'Price' => '€72.99',
            ],
            [
                'Model' => 'HP DL120G7Intel G850',
                'RAM' => '4GBDDR3',
                'HDD' => '4x1TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€39.99',
            ],
            [
                'Model' => 'Dell R730XD2x Intel Xeon E5-2667v4',
                'RAM' => '128GBDDR4',
                'HDD' => '2x120GBSSD',
                'Location' => 'SingaporeSIN-01',
                'Price' => '€364.99',
            ],
            [
                'Model' => 'Dell R730XD2x Intel Xeon E5-2670v3',
                'RAM' => '128GBDDR4',
                'HDD' => '2x120GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€364.99',
            ],[
                'Model' => 'Dell R730XD2x Intel Xeon E5-2650v3',
                'RAM' => '128GBDDR4',
                'HDD' => '4x480GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€279.99',
            ],
            [
                'Model' => 'Dell R730XD2x Intel Xeon E5-2650v4',
                'RAM' => '128GBDDR4',
                'HDD' => '4x480GBSSD',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€286.99',
            ],
            ['rowIndex' => 14],
            ['startrow' => 201],
        ];
        $this->assertEquals($expectedResult, $dataFetched);
    }

    /**
     * Test API by providing range of RAM values.
     */
    public function testReadFileRangeofRam() {
        $absPathOfFile = __DIR__ . '/'. self::FILENAME;
        $params = [
            'storage' => null,
            'ram' => '16GB,32GB,64GB',
            'hdisk' => null,
            'location' => null,
            'limit' => 10,
            'offset' => 1,
        ];
        $data = $this->readspsheet->readFile($absPathOfFile, $params);
        $dataFetched = \json_decode($data, true);
        $expectedResult = [
            [
                'Model' => 'Dell R210Intel Xeon X3440',
                'RAM' => '16GBDDR3',
                'HDD' => '2x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€49.99',
            ],
            [
                'Model' => 'HP DL180G62x Intel Xeon E5620',
                'RAM' => '32GBDDR3',
                'HDD' => '8x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€119.00',
            ],
            [
                'Model' => 'HP DL380eG82x Intel Xeon E5-2420',
                'RAM' => '32GBDDR3',
                'HDD' => '8x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€131.99',
            ],
            [
                'Model' => 'RH2288v32x Intel Xeon E5-2620v4',
                'RAM' => '64GBDDR4',
                'HDD' => '4x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€161.99',
            ],
            [
                'Model' => 'Dell R210-IIIntel Xeon E3-1230v2',
                'RAM' => '16GBDDR3',
                'HDD' => '2x2TBSATA2',
                'Location' => 'FrankfurtABC-01',
                'Price' => '€72.99',
            ],
            [
                'Model' => 'HP DL380pG82x Intel Xeon E5-2650',
                'RAM' => '64GBDDR3',
                'HDD' => '8x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€179.99',
            ],
            [
                'Model' => 'IBM X36302x Intel Xeon E5620',
                'RAM' => '32GBDDR3',
                'HDD' => '8x2TBSATA2',
                'Location' => 'AmsterdamAMS-01',
                'Price' => '€106.99',
            ],
            ['rowIndex' => 14],
            ['startrow' => 201],
        ];
        $this->assertEquals($expectedResult, $dataFetched);
    }
}
