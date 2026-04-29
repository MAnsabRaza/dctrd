<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\CrossSellingRelation;
use App\Models\Product;
use App\Models\Webinar;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class CrossSellingRelationController extends Controller
{
    private $path;

    public function __construct()
    {
        $this->path = getTemplate() . '.panel.cross_sellings.';
    }

    public function index()
    {
        $relations = CrossSellingRelation::all()->groupBy(function($item) {
            $source_type = ($item->source_type == 'App\Models\Webinar') ? trans('cross.Course') : trans('cross.'.class_basename($item->source_type));
            return optional($item->source)->title.' - ('.class_basename($source_type).')' ?? 'N/A';
        });
        return view($this->path . 'index', compact('relations'));
    }

    public function create()
    {
        $courses = Webinar::all();
        $products = Product::all();
        $articles = Blog::all();

        return view($this->path . 'create', compact('courses', 'products', 'articles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'source_type' => 'required',
            'source_id'   => 'required|integer',
            'target_type' => 'required',
            'target_id'   => 'required|integer',
        ]);

        CrossSellingRelation::create($request->all());

        return redirect()->route('cross-sellings.index')->with('success', 'Relation created successfully.');
    }

    public function destroy(CrossSellingRelation $crossSelling)
    {
        $crossSelling->delete();
        return redirect()->back()->with('success', 'Deleted successfully.');
    }

    public function search(Request $request)
    {
        $type = $request->input('type');
        $q = $request->input('q');
        $source_type = $request->input('source_type');
        $source_id = $request->input('source_id');

        $model = $this->getModelByType($type);

        if (!$model) {
            return response()->json([], 404);
        }

        $query = $model;

        if ($source_id) {
            $query->where('id', '!=', $source_id);
        }

        if ($q) {
            $query->whereTranslationLike('title', "%$q%");
        }

        $results = $query->get();

        return response()->json($results);
    }

    private function getModelByType($type)
    {
        switch ($type) {
            case 'App\Models\Webinar':
                return Webinar::where('teacher_id', auth()->user()->id)->where('status', 'active');
            case 'App\Models\Product':
                return Product::where('creator_id', auth()->user()->id)->where('status', 'active');
            case 'App\Models\Blog':
                return Blog::where('author_id', auth()->user()->id)->where('status', 'publish');
            default:
                return null;
        }
    }
}
