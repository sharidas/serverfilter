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

use App\Entity\FormData;
use App\Form\TaskType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ViewController extends AbstractController
{
    /**
     * This method is the route from view.
     * @Route("/getResult", name="viewserver")
     */
    public function viewPage(Request $request)
    {
        $task = new FormData();
        $task->setStorage('0');
        //$task->setRam(['2GB']);
        $task->setHdisk('SAS');
        $task->setLocation('AmsterdamAMS-01');
        $task->setData([]);
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $request->files->get('task')['uploadFile'];
            $fileName = $file->getClientOriginalName();
            $file->move($this->getParameter( 'file_directory' ), $fileName);
            //Set the request to have api key
            $request->headers->set('filter-api-key', $this->getParameter('secret'));
            $result = $this->forward('App\Controller\ServerFilterController::getServerData', ['request' => $request]);
            $task->setData(\json_decode($result->getContent(), true));
            $content = $this->renderView('view/index.html.twig', [
                'form' => $form->createView(),
                'data' => \json_decode($result->getContent(), true)
            ]);
            return new Response($content);
        }
        return $this->render('view/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
