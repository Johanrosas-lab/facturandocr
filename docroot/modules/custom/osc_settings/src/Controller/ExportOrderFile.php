<?php

namespace Drupal\osc_settings\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ExportOrderFile extends ControllerBase {
    public function downloadItem($item_id) {
        $fileContent = \Drupal::entityTypeManager()
            ->getStorage('file')
            ->loadByProperties(['fid' => $item_id]);
        $url = file_create_url($fileContent[$item_id]->getFileUri());
        $data = file_get_contents($url);
        $response = new Response($data);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileContent[$item_id]->getFilename()
        );

        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

}