<?php

namespace App\Http\Controllers;

use App\Models\AiKnowledgeBase;
use Illuminate\Http\Request;

class AiKnowledgeBaseController extends Controller
{
    public function index()
    {
        $knowledges = AiKnowledgeBase::latest()->get();
        $pageTitle = 'Kelola AI Knowledge';

        return view('ai_knowledge.index', compact('knowledges', 'pageTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'topik'    => 'required|string|max:255',
            'keywords' => 'required|string',
            'jawaban'  => 'required|string',
        ]);

        AiKnowledgeBase::create([
            'topik'     => $request->topik,
            'keywords'  => $request->keywords,
            'jawaban'   => $request->jawaban,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('ai-knowledge.index')->with('success', 'Data pengetahuan AI berhasil ditambahkan.');
    }

    public function update(Request $request, AiKnowledgeBase $ai_knowledge)
    {
        $request->validate([
            'topik'    => 'required|string|max:255',
            'keywords' => 'required|string',
            'jawaban'  => 'required|string',
        ]);

        $ai_knowledge->update([
            'topik'     => $request->topik,
            'keywords'  => $request->keywords,
            'jawaban'   => $request->jawaban,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('ai-knowledge.index')->with('success', 'Data pengetahuan AI berhasil diperbarui.');
    }

    public function destroy(AiKnowledgeBase $ai_knowledge)
    {
        $ai_knowledge->delete();

        return redirect()->route('ai-knowledge.index')->with('success', 'Data pengetahuan AI berhasil dihapus.');
    }
}
