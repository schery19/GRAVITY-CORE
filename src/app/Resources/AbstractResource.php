<?php

// Tous droits réservés GRAVITY-CORE 2024
// Distribués sous licence MIT
// Voir le fichier LICENSE.txt pour plus de détails

namespace Gravity\Core\App\Resources;


use Gravity\Core\App\ResourceArray;


abstract class AbstractResource implements ResourceArray {

    protected $entity;

    /**
     * Retourne les données de l'entité sous forme de tableau personalisable 
     * @param mixed $data Instance de l'entité
     * @throws \Exception
     * @return array
     */
    public final function make($data) {
        $out = array();

        if(is_array($data)) 
            throw new \Exception("Unable to call ".static::class."::make method with array argument");


        $this->entity = new $this->entity($data->toRender());

        $out = $this->toRender();

        return $out;
    }

    /**
     * Retourne les données sous forme de tableau personalisable 
     * @param mixed $data Instance ou tableau du type de l'entité
     * @throws \Exception
     * @return array
     */
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


    public function toJSON() {
        return json_encode($this->toRender());
    }


    public static function fromJSON(string $json, string $class) {
        $data = json_decode($json, true);

        return new $class($data);
    }


}


?>
