<?php

namespace Modules\CategorySettings\Transformers;

use Illuminate\Http\Resources\Json\Resource;

class CategoryResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'type'       => 'categories',
            'id'         => (string) $this->id,
            'attributes' => [
                'id'              => $this->id,
                'code'            => $this->code,
                'name'            => $this->name,
                'module'          => $this->module,
                'color'           => $this->color,
                'active'          => $this->active,
                'order'           => $this->order,
                'description'     => $this->description,

                // 'created_at'      => $this->created_at->toIso8601String(),
                // 'updated_at'      => $this->updated_at->toIso8601String(),
            ],
        ];
    }
}
