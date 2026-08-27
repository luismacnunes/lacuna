<?php

namespace App\Http\Controllers;

use App\Services\Retrieval\ChunkRetriever;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request, ChunkRetriever $retriever)
    {
        $query = trim((string) $request->input('q', ''));

        $results = $query === ''
            ? []
            : $retriever->search($query);

        return view('search.index', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}