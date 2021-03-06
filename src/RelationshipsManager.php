<?php

namespace Freshbitsweb\Laratables;

class RelationshipsManager
{
    protected $model;

    protected $modelObject;

    protected $relations = [];

    /**
     * Initialize properties.
     *
     * @param \Illuminate\Database\Eloquent\Model The model object to work on
     *
     * @return void
     */
    public function __construct($model, $modelObject)
    {
        $this->model = $model;
        $this->modelObject = $modelObject;
    }

    /**
     * Adds the relation to be loaded with the query.
     *
     * @param string Name of the column
     *
     * @return void
     */
    public function addRelation($columnName)
    {
        $relationName = getRelationName($columnName);

        if (
            ! array_key_exists($relationName, $this->relations) &&
            ! in_array($relationName, $this->relations)
        ) {
            $methodName = camel_case('laratables_'.$relationName.'relation_query');
            if (method_exists($this->model, $methodName)) {
                $this->relations[$relationName] = $this->model::$methodName();

                return;
            }

            $this->relations[] = $relationName;
        }
    }

    /**
     * Returns the (foreign key) column(s) to be selected for the relation table.
     *
     * @param string Name of the column
     *
     * @return array
     */
    public function getRelationSelectColumns($columnName)
    {
        $relationName = getRelationName($columnName);

        return $this->decideRelationColumns($relationName);
    }

    /**
     * Decides the columns to be used based on the relationship.
     *
     * @param string Name of the relation
     *
     * @return array
     */
    protected function decideRelationColumns($relationName)
    {
        // https://stackoverflow.com/a/25472778/3113599
        $relationType = (new \ReflectionClass($this->modelObject->$relationName()))->getShortName();
        $selectColumns = [];

        switch ($relationType) {
            case 'BelongsTo':
                $selectColumns[] = $this->modelObject->$relationName()->getForeignKey();
                break;
            case 'MorphTo':
                $selectColumns[] = $this->modelObject->$relationName()->getForeignKey();
                $selectColumns[] = $this->modelObject->$relationName()->getMorphType();
                break;
        }

        return $selectColumns;
    }

    /**
     * Returns the relations to be loaded by query.
     *
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }
}
