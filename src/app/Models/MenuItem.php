<?php

namespace Backpack\MenuCRUD\app\Models;

use Backpack\LangFileManager\app\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class MenuItem extends Model
{
    use CrudTrait;

    protected $table = 'menu_items';

    protected $fillable = ['name', 'type', 'link', 'page_id', 'parent_id'];
    protected $casts = [
        'name' => 'array',
    ];

    public function parent()
    {
        return $this->belongsTo('Backpack\MenuCRUD\app\Models\MenuItem', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('Backpack\MenuCRUD\app\Models\MenuItem', 'parent_id');
    }

    public function page()
    {
        return $this->belongsTo('Backpack\PageManager\app\Models\Page', 'page_id');
    }

    /**
     * Get all menu items, in a hierarchical collection.
     * Only supports 2 levels of indentation.
     */
    public static function getTree()
    {
        $menu = self::orderBy('lft')->get();

        if ($menu->count()) {
            foreach ($menu as $k => $menu_item) {
                $menu_item->children = collect([]);

                foreach ($menu as $i => $menu_subitem) {
                    if ($menu_subitem->parent_id == $menu_item->id) {
                        $menu_item->children->push($menu_subitem);

                        // remove the subitem for the first level
                        $menu = $menu->reject(function ($item) use ($menu_subitem) {
                            return $item->id == $menu_subitem->id;
                        });
                    }
                }
            }
        }

        return $menu;
    }

    public function getNamesAttribute()
    {
        return json_decode($this->attributes['name'], TRUE);
    }

    public function getHasChildrenAttribute()
    {
        return count($this->children) !== 0;
    }
    public function getName($lang = null)
    {
        if (!$lang) {
            return $this->name;
        } else {
            $lang = Language::findByAbbr($lang);
            return $this->names[$lang->id];
        }
    }

    public function url()
    {
        switch ($this->type) {
            case 'external_link':
                return $this->link;
                break;

            case 'internal_link':
                $lang = request()->route('lang');
                return url($lang . '/' .$this->link);
                break;

            default: //page_link
                if ($lang = request()->route('lang')) {
                    return route('page',['page' => $this->page->slug, 'lang' => $lang]);
                } else {
                    return route('page',['page' => $this->page->slug, 'lang' => config('app.locale')]);
                }
                break;
        }
    }

    public function isCurrent()
    {
        return (request()->fullUrlIs($this->url()));
    }

    public function __get($key)
    {
        if (preg_match('/(.+)\[(\d+)\]/', $key, $matches)) {
            $value = parent::__get($matches[1]);

            return array_key_exists($matches[2], $value) ? $value[$matches[2]]: '';
        } elseif ($key === 'name') {
            $value = parent::__get($key);

            return array_first($value);
        }

        return parent::__get($key);
    }
}
