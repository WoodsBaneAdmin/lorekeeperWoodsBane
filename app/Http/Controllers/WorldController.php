<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Currency\Currency;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\Item\ItemCategory;
use App\Models\Item\Item;
use App\Models\Feature\FeatureCategory;
use App\Models\Feature\Feature;
use App\Models\Character\CharacterCategory;
use App\Models\Prompt\PromptCategory;
use App\Models\Prompt\Prompt;

class WorldController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | World Controller
    |--------------------------------------------------------------------------
    |
    | Displays information about the world, as entered in the admin panel.
    | Pages displayed by this controller form the site's encyclopedia.
    |
    */

    /**
     * Shows the index page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex()
    {
        return view('world.index');
    }

    /**
     * Shows the currency page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCurrencies(Request $request)
    {
        $query = Currency::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%')->orWhere('abbreviation', 'LIKE', '%'.$name.'%');
        return view('world.currencies', [  
            'currencies' => $query->orderBy('name')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the rarity page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getRarities(Request $request)
    {
        $query = Rarity::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.rarities', [  
            'rarities' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the species page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSpecieses(Request $request)
    {
        $query = Species::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.specieses', [  
            'specieses' => $query->with(['subtypes' => function($query) {
                $query->orderBy('sort', 'DESC');
            }])->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the subtypes page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSubtypes(Request $request)
    {
        $query = Subtype::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.subtypes', [  
            'subtypes' => $query->with('species')->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }
    
    /**
     * Shows the item categories page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getItemCategories(Request $request)
    {
        $query = ItemCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.item_categories', [  
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }
    
    /**
     * Shows the trait categories page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFeatureCategories(Request $request)
    {
        $query = FeatureCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.feature_categories', [  
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }
    
    /**
     * Shows the traits page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getFeatures(Request $request)
    {
        $query = Feature::with('category')->with('rarity')->with('species');
        $data = $request->only(['rarity_id', 'feature_category_id', 'species_id', 'name', 'sort']);
        if(isset($data['rarity_id']) && $data['rarity_id'] != 'none') 
            $query->where('rarity_id', $data['rarity_id']);
        if(isset($data['feature_category_id']) && $data['feature_category_id'] != 'none') 
            $query->where('feature_category_id', $data['feature_category_id']);
        if(isset($data['species_id']) && $data['species_id'] != 'none') 
            $query->where('species_id', $data['species_id']);
        if(isset($data['name'])) 
            $query->where('name', 'LIKE', '%'.$data['name'].'%');

        if(isset($data['sort'])) 
        {
            switch($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'category':
                    $query->sortCategory();
                    break;
                case 'rarity':
                    $query->sortRarity();
                    break;
                case 'rarity-reverse':
                    $query->sortRarity(true);
                    break;
                case 'species':
                    $query->sortSpecies();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortOldest();
                    break;
            }
        } 
        else $query->sortCategory();

        return view('world.features', [
            'features' => $query->paginate(20)->appends($request->query()),
            'rarities' => ['none' => 'Any Rarity'] + Rarity::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'specieses' => ['none' => 'Any Species'] + Species::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'categories' => ['none' => 'Any Category'] + FeatureCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows the items page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getItems(Request $request)
    {
        $query = Item::with('category');
        $data = $request->only(['item_category_id', 'name', 'sort']);
        if(isset($data['item_category_id']) && $data['item_category_id'] != 'none') 
            $query->where('item_category_id', $data['item_category_id']);
        if(isset($data['name'])) 
            $query->where('name', 'LIKE', '%'.$data['name'].'%');

        if(isset($data['sort'])) 
        {
            switch($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'category':
                    $query->sortCategory();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortOldest();
                    break;
            }
        } 
        else $query->sortCategory();

        return view('world.items', [
            'items' => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + ItemCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows the character categories page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterCategories(Request $request)
    {
        $query = CharacterCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%')->orWhere('code', 'LIKE', '%'.$name.'%');
        return view('world.character_categories', [  
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }
    
    /**
     * Shows the prompt categories page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPromptCategories(Request $request)
    {
        $query = PromptCategory::query();
        $name = $request->get('name');
        if($name) $query->where('name', 'LIKE', '%'.$name.'%');
        return view('world.prompt_categories', [  
            'categories' => $query->orderBy('sort', 'DESC')->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the prompts page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPrompts(Request $request)
    {
        $query = Prompt::active()->with('category');
        $data = $request->only(['prompt_category_id', 'name', 'sort']);
        if(isset($data['prompt_category_id']) && $data['prompt_category_id'] != 'none') 
            $query->where('prompt_category_id', $data['prompt_category_id']);
        if(isset($data['name'])) 
            $query->where('name', 'LIKE', '%'.$data['name'].'%');

        if(isset($data['sort'])) 
        {
            switch($data['sort']) {
                case 'alpha':
                    $query->sortAlphabetical();
                    break;
                case 'alpha-reverse':
                    $query->sortAlphabetical(true);
                    break;
                case 'category':
                    $query->sortCategory();
                    break;
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortOldest();
                    break;
                case 'start':
                    $query->sortStart();
                    break;
                case 'start-reverse':
                    $query->sortStart(true);
                    break;
                case 'end':
                    $query->sortEnd();
                    break;
                case 'end-reverse':
                    $query->sortEnd(true);
                    break;
            }
        } 
        else $query->sortCategory();

        return view('world.prompts', [
            'prompts' => $query->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + PromptCategory::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray()
        ]);
    }
}