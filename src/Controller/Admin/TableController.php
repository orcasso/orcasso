<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Table\TableExporter;
use App\Table\TableFactoryCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[\Symfony\Component\Routing\Attribute\Route('/table')]
#[IsGranted(User::ROLE_ADMIN_ACTIVITY_EDIT)]
class TableController extends AbstractController
{
    #[\Symfony\Component\Routing\Attribute\Route('/export-table/{id}', name: 'admin_table_export')]
    public function export(Request $request, TableExporter $exporter, TableFactoryCollection $factoryCollection, string $id): StreamedResponse
    {
        $writer = $exporter->exportToSpreadsheet($factoryCollection->get($id)->getTable(), $request);
        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $response->headers->set('Content-Type', 'application/vnd.oasis.opendocument.spreadsheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$id.'-'.date('Ymd_His').'.ods"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
