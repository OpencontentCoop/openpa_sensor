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
            'id' => 'ID',
            'privacy' => 'Privacy',
            'moderation' => 'Moderazione',
            'type' => 'Tipo',
            'current_status' => 'Stato corrente',
            'created' => 'Creato il',
            'modified'  => 'Ultima modifica del',
            'expiring_date' => 'Scadenza',
            'resolution_time' => 'Data risoluzione',
            'resolution_diff' => 'Tempo di risoluzione',
            'title' => 'Titolo',
            'author' => 'Autore',
            'category' => 'Area tematica',
            'current_owner' => 'Assegnatario',
            'human_message_count' => 'Commenti'
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
    
    protected function transformItem( eZCollaborationItem $item )
    {        
        $content = $item->attribute( 'content' );
        $helper = $content['helper'];
        $post = $helper->attribute( 'sensor' );     
        $data = array_fill_keys( array_keys( $this->CSVheaders ), '');
        $data['id'] = $helper->attribute( 'object' )->attribute( 'id' );
        
        $privacy = $post->attribute( 'current_privacy_status' );
        $data['privacy'] = $privacy['name'];
        
        $moderation = $post->attribute( 'current_moderation_status' );
        $data['moderation'] = $moderation['name'];
        
        $type = $post->attribute( 'type' );
        $data['type'] = $type['name'];

        $currentStatus = $post->attribute( 'current_status' );
        $data['current_status'] = $currentStatus['name'];
        
        $data['created'] = strftime( '%d/%m/%Y %H:%M', $item->attribute( 'created' ) );
        $data['modified'] = strftime( '%d/%m/%Y %H:%M', $item->attribute( 'modified' ) );
        
        $expiringDate = $helper->attribute( 'expiring_date' );
        $data['expiring_date'] = strftime( '%d/%m/%Y %H:%M', $expiringDate['timestamp'] );
    
        $resolutionTime = $helper->attribute( 'resolution_time' );
        $data['resolution_time'] = $resolutionTime['timestamp'] ? strftime( '%d/%m/%Y %H:%M', $resolutionTime['timestamp'] ) : '';
        $data['resolution_diff'] = $resolutionTime['text'];
        
        $data['title'] = $helper->attribute( 'object' )->attribute( 'name' );        
        
        $data['author'] = $post->attribute( 'post_author_name' );
        $data['category'] = $post->attribute( 'post_category_name' );
        $data['current_owner'] = $post->attribute( 'current_owner' ) ? $post->attribute( 'current_owner' ) : '';
        $data['human_message_count'] = $helper->attribute( 'human_message_count' );
        
        return $data;
    }
}
