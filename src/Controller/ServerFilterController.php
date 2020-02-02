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
namespace App\Controller;

use App\Service\ReadSpreadSheet;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServerFilterController extends AbstractController
{
    /**
     * This method access the ReadSpreadSheet service to retrieve the data in a JSON format.
     * Data validation check is made in this method
     *
     * @Route("/filterResult", name="filterresult")
     */
    public function getServerData(Request $request, LoggerInterface $logger, ReadSpreadSheet $readSpreadSheet) {
        /**
         * If the API Key does not match with the one provided send a bad request response.
         */
        if($request->headers->get('filter-api-key') !== $this->getParameter('secret')) {
            return new JsonResponse(['data' => 'Unauthorized Access'], Response::HTTP_BAD_REQUEST);
        }
        /**
         * Check if the request comes by submitting a form?
         */
        $storageFilter = null;
        $ramStorageFilter = null;
        $hardDiskTypeFilter = null;
        $locationTypeFilter = null;
        $limit = 30;
        $offset = 1;
        $file = null;

        if ($request->request->get('task') === null) {
            $storageFilter = $request->get('storage');
            $ramStorageFilter = $request->get('ram');
            $hardDiskTypeFilter = $request->get('hdisk');
            $locationTypeFilter = $request->get('location');
            $file = $request->get('file');
            if ($file === null) {
                return new JsonResponse(['data' => 'Unauthorized access'], Response::HTTP_FORBIDDEN);
            }
            $limit = ($request->get('limit') !== null) ? $request->get('limit') : $limit;
            $offset = ($request->get('offset') !== null) ? $request->get('offset') : $offset;
        } else {
            $requestForm = $request->get('task');
            if (isset($requestForm['storage'])) {
                $storageFilter = $requestForm['storage'];
            }

            if (isset($requestForm['ram'])) {
                $ramStorageFilter = $requestForm['ram'];
                if (\is_array($ramStorageFilter) && isset($ramStorageFilter[1])) {
                    $ramStorageFilter = \implode(',', $ramStorageFilter);
                }
            }

            if ($requestForm['hdisk']) {
                $hardDiskTypeFilter = $requestForm['hdisk'];
            }

            if ($requestForm['location']) {
                $locationTypeFilter = $requestForm['location'];
            }

            $file = $this->getParameter( 'file_directory' ) . '/' . $request->files->get('task')['uploadFile']->getClientOriginalName();

        }

        /**
         * Create a filter array
         */
        $filterParams = [
            'storage' => $storageFilter,
            'ram' => $ramStorageFilter,
            'hdisk' => $hardDiskTypeFilter,
            'location' => $locationTypeFilter,
            'limit' => $limit,
            'offset' => $offset,
        ];

        $data = $readSpreadSheet->readFile($file, $filterParams);
        return new JsonResponse(\json_decode($data), Response::HTTP_OK);
    }
}
