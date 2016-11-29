<?php

namespace Backpack\MenuCRUD\app\Http\Controllers\Admin;

use App\Http\Requests;
use Backpack\CRUD\app\Http\Controllers\CrudController;
// VALIDATION: change the requests to match your own file names if you need form validation
use Backpack\CRUD\app\Http\Requests\CrudRequest as StoreRequest;
use Backpack\CRUD\app\Http\Requests\CrudRequest as UpdateRequest;
use Backpack\LangFileManager\app\Models\Language;

class MenuItemCrudController extends CrudController
{
    public function __construct()
    {
        parent::__construct();

        $this->crud->setModel("Backpack\MenuCRUD\app\Models\MenuItem");
        $this->crud->setRoute(config('backpack.base.route_prefix').'/menu-item');
        $this->crud->setEntityNameStrings('menu item', 'menu items');

        $this->crud->allowAccess('reorder');
        $this->crud->enableReorder('name', 2);

        $this->crud->addColumn([
                                'name' => 'name',
                                'label' => 'Label',
                            ]);
        $this->crud->addColumn([
                                'label' => 'Parent',
                                'type' => 'select',
                                'name' => 'parent_id',
                                'entity' => 'parent',
                                'attribute' => 'name',
                                'model' => "\Backpack\MenuCRUD\app\Models\MenuItem",
                            ]);

        $languages = Language::getActiveLanguagesArray();

        foreach($languages as $language) {
            $this->crud->addField([
                'name' => 'name[' . $language['id'] . ']',
                'label' => '名稱(' . $language['native'] . ')',
            ]);
        }
//        $this->crud->addField([
//            'name' => 'name',
//            'label' => '名稱',
//        ]);

        $this->crud->addField([
                                'label' => '上層項目',
                                'type' => 'select',
                                'name' => 'parent_id',
                                'entity' => 'parent',
                                'attribute' => 'name',
                                'model' => "\Backpack\MenuCRUD\app\Models\MenuItem",
                            ]);
        $this->crud->addField([
                                'name' => 'type',
                                'label' => '類型',
                                'type' => 'page_or_link',
                                'page_model' => '\Backpack\PageManager\app\Models\Page',
                            ]);
    }

    public function store(StoreRequest $request)
    {
        return parent::storeCrud($request);
    }

    public function update(UpdateRequest $request)
    {
        return parent::updateCrud($request);
    }
}
