<?php

namespace App\Controller\Admin;

use App\Security\Glide;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TempFilesController extends AbstractController
{
    /**
     * @Route("/temp/files", name="temp_files")
     */
    public function index(Request $request): Response
    {
        $glide = new Glide();
        $filesystem = new Filesystem();
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

        $finder->files()->name(['*.png', '*.jpg', '*.jpeg'])->sortByModifiedTime()->in($tempPath);

        if ( $finder->hasResults() ) {
            $iterator = $finder->getIterator();
            $iterator->rewind();
            $firstFile = $iterator->current();
            $firstFileDate = date('Y-m-d', $firstFile->getATime());
        }

        $finder = new Finder();

        $tempDate = $firstFileDate;

        while( $tempDate <= date('Y-m-d') ){
            $months[] = $tempDate;

            $tempDate = date('Y-m-d', strtotime(date('Y-m-t', strtotime($tempDate)) . ' +1 DAY'));
        }

        $finder->files()->date('>= ' . $year . '-' . $month . '-01')->date('<= ' . date('Y-m-t', strtotime($year . '-' . $month . '-30')))->name(['*.png', '*.jpg', '*.jpeg'])->sortByModifiedTime()->in($tempPath);

        if ( $finder->hasResults() ) {
            foreach( $finder as $file ) {
                $relativePath = $file->getRelativePath();
                $addDate = $file->getATime();
                $files[] = [
                    'url' => $glide->generateUrl($relativePath, $file->getFileName()),
                    'relativePath' => $relativePath,
                    'firstFileDate' => $firstFileDate,
                    'addDate' => $addDate,
                ];
            }
        }

        return $this->render('admin/temp_files/index.html.twig', [
            'controller_name' => 'TempFilesController',
            'files' => $files,
            'months' => $months,
        ]);
    }
}
