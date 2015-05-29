<?php

class SensorPostCsvExporter
{
    protected $filters;

    protected $group;

    protected $CSVheaders = array();
    
    public $options = array(
        'CSVDelimiter' => ';',
        'CSVEnclosure' => '"'
    );
    
    public function __construct( array $filters, eZCollaborationGroup $group, $selectedList = null )
    {
        $this->filters = $filters;
        $this->group = $group;
        $this->CSVheaders = array(
            'id'                => ezpI18n::tr( 'openpa_sensor/export', 'ID' ),
            'privacy'           => ezpI18n::tr( 'openpa_sensor/export', 'Privacy' ),
            'moderation'        => ezpI18n::tr( 'openpa_sensor/export', 'Moderazione' ),
            'type'              => ezpI18n::tr( 'openpa_sensor/export', 'Tipo' ),
            'current_status'    => ezpI18n::tr( 'openpa_sensor/export', 'Stato corrente' ),
            'created'           => ezpI18n::tr( 'openpa_sensor/export', 'Creato il' ),
            'modified'          => ezpI18n::tr( 'openpa_sensor/export', 'Ultima modifica del' ),
            'expiring_date'     => ezpI18n::tr( 'openpa_sensor/export', 'Scadenza' ),
            'resolution_time'   => ezpI18n::tr( 'openpa_sensor/export', 'Data risoluzione' ),
            'resolution_diff'   => ezpI18n::tr( 'openpa_sensor/export', 'Tempo di risoluzione' ),
            'title'             => ezpI18n::tr( 'openpa_sensor/export', 'Titolo' ),
            'author'            => ezpI18n::tr( 'openpa_sensor/export', 'Autore' ),
            'category'          => ezpI18n::tr( 'openpa_sensor/export', 'Area tematica' ),
            'current_owner'     => ezpI18n::tr( 'openpa_sensor/export', 'Assegnatario' ),
            'comment'           => ezpI18n::tr( 'openpa_sensor/export', 'Commenti' )
        );
        $this->filename = 'posts'; //@todo
    }
    
    public function handleDownload()
    {        
        $filename = $this->filename . '.csv';
        header( 'X-Powered-By: eZ Publish' );
        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( "Content-Disposition: attachment; filename=$filename" );
        header( "Pragma: no-cache" );
        header( "Expires: 0" );

        $listTypes = SensorHelper::availableListTypes();
        $runOnce = false;
        foreach( $listTypes as $type )
        {
            $count = call_user_func( $type['count_function'], $this->filters, $this->group );
            $length = 50;
            $offset = 0;
            $output = fopen('php://output', 'w');            
            do
            {
                $items = call_user_func( $type['list_function'], $this->filters, $this->group, $length, $offset );
    
                foreach ( $items as $item )
                {            
                    $values = $this->transformItem( $item );
                    if ( !$runOnce )
                    {
                        fputcsv( $output, array_values( $this->CSVheaders ), $this->options['CSVDelimiter'], $this->options['CSVEnclosure'] );
                        $runOnce = true;
                    }
                    fputcsv( $output, $values, $this->options['CSVDelimiter'], $this->options['CSVEnclosure'] );
                    flush();
                }            
                $offset += $length;
                
            } while ( count( $items ) == $length );    
        }
    }
    
    protected function transformItem( SensorHelper $item )
    {
        $data = array_fill_keys( array_keys( $this->CSVheaders ), '');
        $data['id'] = $item->attribute( 'id' );
        
        $privacy = $item->attribute( 'current_privacy_state' );
        $data['privacy'] = $privacy['name'];
        
        $moderation = $item->attribute( 'current_moderation_state' );
        $data['moderation'] = $moderation['name'];
        
        $type = $item->attribute( 'type' );
        $data['type'] = $type['name'];

        $currentStatus = $item->attribute( 'current_object_state' );
        $data['current_status'] = $currentStatus['name'];

        /** @var eZContentObject $object */
        $object = $item->attribute( 'object' );
        $data['created'] = strftime( '%d/%m/%Y %H:%M', $object->attribute( 'published' ) );
        $data['modified'] = strftime( '%d/%m/%Y %H:%M', $object->attribute( 'modified' ) );
        
        $expiringDate = $item->attribute( 'expiring_date' );
        $data['expiring_date'] = strftime( '%d/%m/%Y %H:%M', $expiringDate['timestamp'] );
    
        $resolutionTime = $item->attribute( 'resolution_time' );
        $data['resolution_time'] = $resolutionTime['timestamp'] ? strftime( '%d/%m/%Y %H:%M', $resolutionTime['timestamp'] ) : '';
        $data['resolution_diff'] = $resolutionTime['text'];
        
        $data['title'] = $object->attribute( 'name' );
        
        $data['author'] = $item->attribute( 'author_name' );
        $data['category'] = $item->attribute( 'category_name' );
        $data['current_owner'] = $item->attribute( 'current_owner' ) ? $item->attribute( 'current_owner' ) : '';
        $data['comment'] = $item->attribute( 'comment_count' );
        
        return $data;
    }
}
