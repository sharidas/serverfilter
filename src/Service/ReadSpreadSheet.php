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

namespace App\Service;


use PhpOffice\PhpSpreadsheet\IOFactory;
use Psr\Log\LoggerInterface;

class ReadSpreadSheet
{
    private $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Compute the storage value
     * @param string $storageVal
     * @return int
     */
    private function computeStorage($storageVal) {
        $computedValue = 1;
        $storageArray = explode("x", $storageVal);
        foreach ($storageArray as $value) {
            $computedValue = $computedValue * $value;
        }
        return $computedValue;
    }

    /**
     * Get the storage result by converting TB into GB for comparision
     * In this method the computedStorage, min and max values are converted to GB
     * for comparison. If all are in GB then no conversion is applied.
     *
     * @param int $computedStorage
     * @param string $compStorageType
     * @param null|string $minVal
     * @param null|string $maxVal
     * @return bool
     */
    private function getStorageRangeResult($computedStorage, $compStorageType, $minVal, $maxVal) {
        $result = false;
        preg_match("/^(\d+)(GB|TB)/", $minVal, $minMatches);
        preg_match("/^(\d+)(GB|TB)/", $maxVal, $maxMatches);

        /**
         * Convert the computed storage to GB
         */
        if ($compStorageType === 'TB') {
            $computedStorage = $computedStorage * 1024;
        }

        /**
         * Convert TB to GB
         */
        $convertedMinGB = 0;
        $convertedMaxGB = 0;
        if (isset($minMatches[1]) && isset($minMatches[2])) {
            if ($minMatches[2] === 'TB') {
                $convertedMinGB = 1024 * $minMatches[1];
            }
        }
        if (isset($maxMatches[1]) && isset($maxMatches[2])) {
            if ($maxMatches[2] === 'TB') {
                $convertedMaxGB = 1024 * $maxMatches[1];
            }
        }

        /**
         * If the min range is in GB
         */
        if ($convertedMinGB === 0) {
            if ((isset($minMatches[1])) && $computedStorage >= $minMatches[1]) {
                $result = true;
            }
        } else {
            if ($computedStorage >= $convertedMinGB) {
                $result = true;
            } else {
                $result = false;
            }
        }

        /**
         * if the max range is in GB
         */
        if ($convertedMaxGB === 0) {
            if ((isset($maxMatches[1])) && $computedStorage <= $maxMatches[1]) {
                $result = true;
            }
        } else {
            if ($computedStorage <= $convertedMaxGB) {
                $result = true;
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Validate the storage value matches with the filtervalue
     * @param string $matches
     * @param string $filterValue
     * @return bool
     */
    private function filterStorage($matches, $filterValue) {
        $filterValues = explode('-', $filterValue);
        $minVal = $filterValues[0];
        $maxVal = null;
        if (isset($filterValues[1])) {
            $maxVal  = $filterValues[1];
        }

        if (isset($matches[1]) && isset($matches[2])) {
            $computedStorage = $this->computeStorage($matches[1]);
            /**
             * Check if the storage falls under the range provided
             */
            if ($maxVal !== null) {
                return $this->getStorageRangeResult($computedStorage, $matches[2], $minVal, $maxVal);
            }
            $computedStorage .= $matches[2];
            return ($computedStorage === $filterValue);
        }
        return false;
    }

    /**
     * Validates the harddisk value matches with the filter value
     * @param string $matches
     * @param string $filterValue
     * @return bool
     */
    private function filterHarddisk($matches, $filterValue) {
        if (!isset($matches[3])) {
            return false;
        }
        return ($matches[3] === $filterValue);
    }

    /**
     * Validates the ram value matches with the filter value
     * @param string $ramProperties
     * @param string $filterValue
     * @return bool
     */
    private function filterByRAM($ramProperties, $filterValue) {
        $filterValues = explode(',', $filterValue);
        preg_match("/^(\\d+GB).*/", $ramProperties,$matches);
        if (!isset($matches[1])) {
            return false;
        }

        if (!isset($filterValues[1])) {
            return ($matches[1] === $filterValue);
        }

        if (isset($filterValues[1])) {
            return in_array($matches[1], $filterValues);
        }
    }

    /**
     * Populate the output which needs to be pushed to the filteredOutput data.
     * @param array $output
     * @param string $cellValue
     * @param string $columnName
     * @return array
     */
    private function populateOutput($output, $cellValue, $columnName) {

        if ($columnName === 'A') {
            $output['Model'] = $cellValue;
        }
        if ($columnName === 'B') {
            $output['RAM'] = $cellValue;
        }
        if ($columnName === 'C') {
            $output['HDD'] = $cellValue;
        }
        if ($columnName === 'D') {
            $output['Location'] = $cellValue;
        }
        if ($columnName === 'E') {
            $output['Price'] = $cellValue;
        }
        return $output;
    }

    /**
     * Read the spreadsheet file and return the json data
     * The data is read in chunks so the memory is not clogged.
     *
     * @param $file
     * @param $filterParams
     * @return false|string
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function readFile($file, $filterParams) {
        $counter = 1;
        $limit = 30;
        $startRow = 2;
        $gotAllOutput = false;
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $highestRow = $reader->listWorksheetInfo($file)[0]['totalRows'] + 1;


        $chunkSize = 200;

        $chunkFilter = new ChunkReadFilter();

        $reader->setReadFilter($chunkFilter);

        if (isset($filterParams['offset'])) {
            $startRow = $filterParams['offset'];
        }

        if (isset($filterParams['limit'])) {
            $limit = $filterParams['limit'];
        }

        $filteredOutput = [];
        for (; $startRow < $highestRow; $startRow += $chunkSize) {
            $chunkFilter->setRows($startRow, $chunkSize);
            $spreadSheet = $reader->load($file);

            $workSheet = $spreadSheet->getActiveSheet();

            /**
             * Iterate through the rows and fetch the value and evaluate them.
             */
            foreach ($workSheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $output = [];
                foreach ($cellIterator as $cell) {
                    /**
                     * In this loop we decide whether the row matches with the filter values if so
                     * we populate the output array.
                     */
                    $columnName = $cell->getColumn();
                    $cellValue = $cell->getValue();
                    if ($cellValue === null) {
                        break;
                    }
                    if ($columnName === 'A' && $cellValue === null) {
                        break;
                    }
                    if ($columnName === 'F' || $columnName === 'G' || $columnName === 'H' || $columnName === 'I') {
                        break;
                    }
                    $this->logger->debug("Column and value is ", ['column' => $cell->getColumn(), 'value' => $cell->getValue()]);

                    if (($columnName === "B") && ($filterParams['ram'] !== null)) {
                        $ramFilter = $this->filterByRAM($cellValue, $filterParams['ram']);
                        if (!$ramFilter) {
                            $output = [];
                            break;
                        }
                    }
                    if ($columnName === "C") {
                        preg_match("/^(\d+.*?)(GB|TB)(.*)/", $cellValue, $matches);
                        if ($filterParams['storage'] !== null) {
                            $storageFilter = $this->filterStorage($matches, $filterParams['storage']);
                            if (!$storageFilter) {
                                $output = [];
                                break;
                            }
                        }
                        if ($filterParams['hdisk'] !== null) {
                            $harddiskFilter = $this->filterHarddisk($matches, $filterParams['hdisk']);
                            if (!$harddiskFilter) {
                                $output = [];
                                break;
                            }
                        }
                    }
                    if ($columnName === "D") {
                        if ($filterParams['location'] !== null) {
                            if ($cellValue !== $filterParams['location']) {
                                $output = [];
                                break;
                            }
                        }
                    }
                    $output = $this->populateOutput($output, $cellValue, $columnName);
                }
                if ($output !== []) {
                    if ($counter <= $limit) {
                        $filteredOutput[] = $output;
                        $counter++;
                    } else {
                        $gotAllOutput = true;
                        break;
                    }
                }
            }

            if ($gotAllOutput) {
                $filteredOutput[] = ['rowIndex' => $row->getRowIndex()];
                $filteredOutput[] = ['startrow' => $startRow];
               $this->logger->debug("So the final output is", ['finaloutput' => $filteredOutput]);
               return \json_encode($filteredOutput, JSON_FORCE_OBJECT);
                break;
            }
        }
        if (!$gotAllOutput) {
            $filteredOutput[] = ['rowIndex' => $row->getRowIndex()];
            $filteredOutput[] = ['startrow' => $startRow];
            $this->logger->debug("So the final output is", ['finaloutput' => $filteredOutput]);
            return \json_encode($filteredOutput, JSON_FORCE_OBJECT);
        }
    }
}