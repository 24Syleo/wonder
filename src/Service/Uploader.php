<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Uploader {

    public function __construct(private ContainerInterface $container, private Filesystem $fs){
        $this->container = $container;
    }
    public function uploadProfileImage($picture, $oldPicturePath = null){

        $folder   = $this->container->getParameter('profile.folder');
        $ext      = $picture->guessExtension() ?? 'bin';
        $filename = bin2hex(random_bytes(10)) . '.' . $ext;;
        $picture->move($folder, $filename);
        if ($oldPicturePath) {
            $this->fs->remove($folder . '/' . pathinfo($oldPicturePath, PATHINFO_BASENAME));
        }
        return $this->container->getParameter('profile.folder.public_path') . '/' . $filename;
    }
}

?>