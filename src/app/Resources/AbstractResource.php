<?php

// Tous droits réservés GRAVITY-CORE 2024
// Distribués sous licence MIT
// Voir le fichier LICENSE.txt pour plus de détails

namespace Gravity\Core\App\Resources;


use Gravity\Core\App\ResourceArray;


abstract class AbstractResource implements ResourceArray {

    protected $entity;


    public final function make($data) {
        $out = array();

        if(is_array($data)) 
            throw new \Exception("Unable to call ".static::class."::make method with array argument");


        $this->entity = new $this->entity($data->toRender());

        $out = $this->toRender();

        return $out;
    }


    public final function collection($data) {
        $out = array();

        if(!is_array($data)) {
            $this->entity = new $this->entity($data->toRender());

            $out[] = $this->toRender();

            return $out;
        }

        foreach($data as $element) {

            $this->entity = new $this->entity($element->toRender());

            $out[] = $this->toRender();

        }

        return $out;
    }


    public function toRender() {
        return $this->toArray();
    }


}


?>