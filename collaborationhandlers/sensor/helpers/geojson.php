<?php

class SensorGeoJsonFeatureCollection
{
    public $type = 'FeatureCollection';
    public $features = array();
    
    public function add( SensorGeoJsonFeature $feature )
    {
        $this->features[] = $feature;
    }
}

class SensorGeoJsonFeature
{
    public $type = "Feature";
    public $id;
    public $properties;
    public $geometry;
    
    public function __construct( $id, array $geometryArray, array $properties )    
    {
        $this->id = $id;
        
        $this->geometry = new SensorGeoJsonGeometry();
        $this->geometry->coordinates = $geometryArray;
        
        $this->properties = new SensorGeoJsonProperties( $properties );        
    }
}

class SensorGeoJsonGeometry
{
    public $type = "Point";
    public $coordinates;
}

class SensorGeoJsonProperties
{
    public function __construct( array $properties = array() )
    {
        foreach( $properties as $key => $value )
        {
            $this->{$key} = $value;
        }
    }
}