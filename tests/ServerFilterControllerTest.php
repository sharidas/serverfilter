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


use App\Controller\ServerFilterController;
use App\Service\ReadSpreadSheet;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerFilterControllerTest extends TestCase
{
    private $logger;
    private $request;
    private $readSpSheet;
    private $headerBag;
    private $parameterBag;
    private $containerInterface;
    private $serverFilterController;
    protected function setUp()
    {
        parent::setUp();
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->request = $this->createMock(Request::class);
        $this->readSpSheet = $this->createMock(ReadSpreadSheet::class);
        $this->headerBag = $this->createMock(HeaderBag::class);
        $this->parameterBag = $this->createMock(ParameterBag::class);
        $this->containerInterface = $this->createMock(ContainerInterface::class);
        $this->serverFilterController = new ServerFilterController();
    }

    private function initializeRequest($actualSecret, $expectedSecret, $initializeParameterBag=true) {
        $this->headerBag->method('get')
            ->with('filter-api-key')
            ->willReturn($actualSecret);
        $this->request->headers = $this->headerBag;

        if ($initializeParameterBag) {
            $this->parameterBag->method('get')
                ->willReturn($expectedSecret);
        }
        $this->containerInterface->method('has')
            ->willReturn(true);
        $this->containerInterface->method('get')
            ->with('parameter_bag')
            ->willReturn($this->parameterBag);
    }

    public function testWithoutFileInput() {
        $this->initializeRequest('123', '1');

        $this->serverFilterController->setContainer($this->containerInterface);

        $response = $this->serverFilterController->getServerData($this->request, $this->logger, $this->readSpSheet);
        $expectedResponse = new JsonResponse(['data' => 'Unauthorized Access'], Response::HTTP_BAD_REQUEST);
        $this->assertEquals($response, $expectedResponse);
    }

    public function testNoFileProvided() {
        $this->initializeRequest('123', '123', false);
        $this->parameterBag->method('get')
            ->willReturnOnConsecutiveCalls('123', null);
        $this->request->request = $this->parameterBag;
        $this->serverFilterController->setContainer($this->containerInterface);
        $response = $this->serverFilterController->getServerData($this->request, $this->logger, $this->readSpSheet);
        $expectedResponse = new JsonResponse(['data' => 'Unauthorized access'], Response::HTTP_FORBIDDEN);
        $this->assertEquals($response, $expectedResponse);
    }

    public function testLimitAndOffsetWhenNotProvided() {
        $this->initializeRequest('123', '123', false);
        $this->parameterBag->method('get')
            ->willReturnOnConsecutiveCalls('123', null);
        $this->request->method('get')
            ->willReturnOnConsecutiveCalls(null, null, null, null, 'foo.txt');
        $this->request->request = $this->parameterBag;
        $this->serverFilterController->setContainer($this->containerInterface);

        $this->readSpSheet->expects($this->once())
            ->method('readFile')
            ->with('foo.txt',
                [
                    'storage' => null, 'ram' => null,
                    'hdisk' => null, 'location' => null,
                    'limit' => 30, 'offset' => 1
                ]);

        $response = $this->serverFilterController->getServerData($this->request, $this->logger, $this->readSpSheet);
        $expectedResponse = new JsonResponse(\json_decode(null), Response::HTTP_OK);
        $this->assertEquals($response, $expectedResponse);
    }

    public function testFormData() {
        $this->initializeRequest('123', '123', false);
        $uploadFile = $this->createMock(UploadedFile::class);
        $uploadFile->method('getClientOriginalName')
            ->willReturn('foo.txt');
        $this->parameterBag->method('get')
            ->willReturnOnConsecutiveCalls(
                '123',
                '',
                '/a/b/c',
                ['uploadFile' => $uploadFile]);
        $this->request->method('get')
            ->willReturnOnConsecutiveCalls(
                [
                    'storage' => '200GB',
                    'ram' => '12GB',
                    'hdisk' => 'SATA',
                    'location' => 'Amsterdam',
                ]
            );
        $this->request->method('get')
            ->willReturnOnConsecutiveCalls(null, null, null, null, 'foo.txt');
        $this->request->request = $this->parameterBag;
        $this->serverFilterController->setContainer($this->containerInterface);

        $this->readSpSheet->expects($this->once())
            ->method('readFile')
            ->with('/a/b/c/foo.txt',
                [
                    'storage' => '200GB', 'ram' => '12GB',
                    'hdisk' => 'SATA', 'location' => 'Amsterdam',
                    'limit' => 30, 'offset' => 1
                ]);


        $this->request->files = $this->parameterBag;
        $response = $this->serverFilterController->getServerData($this->request, $this->logger, $this->readSpSheet);
        $expectedResponse = new JsonResponse(\json_decode(null), Response::HTTP_OK);
        $this->assertEquals($response, $expectedResponse);
    }
}