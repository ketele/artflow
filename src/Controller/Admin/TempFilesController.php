<?php

namespace App\Controller\Admin;

use App\Security\Glide;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TempFilesController extends AbstractController
{
    /**
     * @Route("admin/temp_files", name="temp_files")
     */
    public function index(Request $request): Response
    {
        $glide = new Glide();
        $finder = new Finder();

        $files = [];
        $months = [];
        $firstFileDate = date('Y-m-d');

        $tempPath = sys_get_temp_dir();

        $year = $request->get('year');
        $month = $request->get('month');

        if( !is_numeric($year) || !is_numeric($month) ){
            $year = date('Y');
            $month = date('m');
        }

        $finder->files()
            ->name(['*.png', '*.jpg', '*.jpeg'])
            ->sortByModifiedTime()
            ->in($tempPath);

        if ( $finder->hasResults() ) {
            $iterator = $finder->getIterator();
            $iterator->rewind();
            $firstFile = $iterator->current();
            $firstFileDate = date('Y-m-d', $firstFile->getMTime());
        }

        $finder = new Finder();

        $tempDate = $firstFileDate;

        while( $tempDate <= date('Y-m-d') ){
            $months[] = $tempDate;

            $tempDate = date('Y-m-d', strtotime(date('Y-m-t', strtotime($tempDate)) . ' +1 DAY'));
        }

        $finder->files()
            ->date('>= ' . $year . '-' . $month . '-01')
            ->date('<= ' . date('Y-m-t', strtotime($year . '-' . $month . '-30')))
            ->name(['*.png', '*.jpg', '*.jpeg'])
            ->sortByModifiedTime()
            ->reverseSorting()
            ->in($tempPath);

        if ( $finder->hasResults() ) {
            foreach( $finder as $file ) {
                $relativePath = $file->getRelativePath();
                $addDate = $file->getATime();
                $modifiedDate = $file->getMTime();
                $files[] = [
                    'url' => $glide->generateUrl($relativePath, $file->getFileName()),
                    'relativePath' => $relativePath,
                    'firstFileDate' => $firstFileDate,
                    'addDate' => $addDate,
                    'modifiedDate' => $modifiedDate,
                ];
            }
        }

        return $this->render('admin/temp_files/index.html.twig', [
            'controller_name' => 'TempFilesController',
            'files' => $files,
            'months' => $months,
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * @Route("admin/temp_files_delete_ajax", name="temp_files_delete_ajax")
     */

    public function deleteTempFiles(Request $request): Response
    {
        $jsonData['status'] = true;

        $filesystem = new Filesystem();
        $finder = new Finder();
        $tempPath = sys_get_temp_dir();
        
        $year = $request->get('year');
        $month = $request->get('month');

        if(is_numeric($year) && is_numeric($month)){
            $finder->files()
                ->date('>= ' . $year . '-' . $month . '-01')
                ->date('<= ' . date('Y-m-t', strtotime($year . '-' . $month . '-30')))
                ->name(['*.png', '*.jpg', '*.jpeg'])
                ->sortByModifiedTime()
                ->reverseSorting()
                ->in($tempPath);

            if ( $finder->hasResults() ) {
                foreach( $finder as $file ) {
                    $relativePath = $file->getRelativePath();

                    $file_path = $tempPath . '/' . $relativePath . '/' . $file->getFileName();
                    $filesystem->remove($file_path);
                }
            }
        }else{
            $jsonData['status'] = false;
        }

        $jsonData['year'] = $year;
        $jsonData['month'] = $month;

        return new JsonResponse($jsonData);
    }
}
