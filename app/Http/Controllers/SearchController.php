<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Models\Document;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\Settings;
use DB;

class SearchController extends Controller
{
    public function search() {
        return view('search');
    }

    public function searchPost(SearchRequest $request) {
        $search = !!$request->with_whitespaces ? $request->search : str_replace($request->search, ' ', '');
        
        if (!$request->file('document')) {
            return response([
                'docs' => $this->querySearchInDocumentsContent($search, !!$request->with_whitespaces)->get()
            ]);
        }

        $extension = $request->document->getClientOriginalExtension();
        $file_name = $request->document->getClientOriginalName();
        $file_path = storage_path('app/' . $request->file('document')->storeAs('documents', $file_name));

        $document = new Document([
            'name' => $file_name,
            'extension' => $extension,
        ]);

        if ($extension != 'pdf') {
            $domPdfPath = base_path('vendor/dompdf/dompdf');
            Settings::setPdfRendererPath($domPdfPath);
            Settings::setPdfRendererName('DomPDF');

            $file_path = storage_path('app/documents/' . $file_name);

            //Load word file
            $Content = \PhpOffice\PhpWord\IOFactory::load($file_path);

            //Save it into PDF
            $PDFWriter = \PhpOffice\PhpWord\IOFactory::createWriter($Content,'PDF');
            $PDFWriter->save($file_path);
        }

        $pdfParser = new Parser();
        $pdf = $pdfParser->parseFile($file_path);
        $content = $pdf->getText();


        $content = preg_replace('/[\t+\n+]/', '', $content);
        $content = preg_replace('!\s+!', ' ', $content);

        $document->content = $content;
        $document->save();

        return response([
            'docs' => $this->querySearchInDocumentsContent($search, !!$request->with_whitespaces)->get()
        ]);
    }

    protected function querySearchInDocumentsContent(string $search, bool $with_whitespaces) {
        return Document::where($with_whitespaces ? 'content' : DB::raw("REPLACE(`content`, ' ', '')"), 'LIKE', "%{$search}%");
    }
}
