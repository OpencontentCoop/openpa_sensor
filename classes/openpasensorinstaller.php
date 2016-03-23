<?php

class OpenPASensorInstaller implements OpenPAInstaller
{
    protected $options = array();

    protected $steps = array(
        'a' => '[a] alberatura',
        'r' => '[r] ruoli',
        'c' => '[c] configurazioni ini',
    );

    protected $installOnlyStep;

    protected static $textContent = array(
        'faq'      => "<p>Attraverso la <strong>piattaforma SensorCivico</strong> i/le cittadini/e possono formulare suggerimenti, segnalazioni e reclami su mappa (OpenStreetMap) per il miglioramento della qualità dei servizi offerti e la vivibilità del nucleo abitato.<br>Tutti i suggerimenti, segnalazioni e reclami saranno resi pubblici a meno che il/la cittadino/a non abbia indicato diversamente in fase di caricamento della “segnalazione”.</p><h3>Cosa si intende per suggerimento, segnalazione e reclamo?</h3><p>Per <strong>suggerimento</strong> si intende: qualsiasi proposta o comunicazione, anche di apprezzamento, da parte del cittadino finalizzata al miglioramento della qualità dei servizi offerti. <br>Per <strong>segnalazione</strong> si intende: comunicazione da parte del cittadino rispetto a situazioni di criticità e/o malfunzionamenti (es. segnaletica, illuminazione pubblica, buche nelle strade, ecc…) <br>Per <strong>reclamo</strong> si intende: qualsiasi espressione di insoddisfazione rispetto a disservizi o inefficienze dei servizi offerti.</p><h3>Come avviene l'accesso alla piattaforma SensorCivico?</h3><p>Per poter effettuare l'attività di segnalazione e quindi utilizzare la piattaforma è necessario registrarsi una prima volta attraverso la procedura di registrazione presente nella home “Non sei ancora registrato?”, quindi gli accessi successivi saranno possibili attraverso username e password forniti.<br>Per l'iscrizione e quindi l'accesso al servizio online è sufficiente fornire le seguenti generalità: nome, cognome, indirizzo email e password.</p><h3>Come viene tutelata la riservatezza delle segnalazioni inviate?</h3><p>Vedere <a href=\"[privacy-link]\" title=\"informativa Privacy\">Informazioni/Privacy</a></p><h3>Vengono pubblicati tutti i suggerimenti, segnalazioni e reclami?</h3><p>No, solamente quelli per cui l'autore abbia indicato in fase di caricamento di accettare di renderli pubblici. Tuttavia la redazione si riserva di non rendere pubblici quei reclami, suggerimenti o segnalazioni dai contenuti inopportuni, indecenti, contrari all'ordine pubblico o offensivi della privacy o della dignità delle persone, o comunque non conformi a quanto espressamente stabilito nei termini di utilizzo.</p><h3>Che cosa significa “rendi pubblica questa segnalazione”?</h3><p>Significa che la stessa sarà visibile a tutti coloro che accedono al servizio online.</p><p>Nota: dalle ore 18.00 alle ore 8.00 del giorno successivo e nei giorni festivi e/o di chiusura del Municipio la moderazione da parte della redazione non è attiva, quindi tutte le segnalazioni restano in sospeso e in attesa di pubblicazione.</p><h3>Che cosa significa che i/le cittadini/e possono formulare proposte, suggerimenti e segnalazioni su mappa?</h3><p>Accedendo con il pulsante “segnala” si attiva la procedura di segnalazione che consente di georeferenziare il luogo del suggerimento, segnalazione o reclamo; ossia è possibile indicare la via attraverso lo spostamento sulla mappa del cursore cliccando sul punto che interessa: il puntatore rosso si posizionerà nel punto indicato.</p><h3>E' possibile inviare una segnalazione anonima?</h3><p>Non è possibile inviare una segnalazione anonima perché l'Amministrazione ha bisogno di avere un dato identificativo del segnalante, al fine di accertare la sua volontaria partecipazione all'azione amministrativa; le segnalazioni anonime non verranno prese in considerazione.</p><h3>In che modo è possibile individuare correttamente l'ufficio responsabile per la problematica esposta?</h3><p>Dopo aver indicato l´indirizzo è sufficiente indicare il quartiere, se conosciuto, quindi se si tratta di suggerimento, segnalazione o reclamo, la “segnalazione” viene presa in carico dagli operatori della redazione che provvederanno all'inoltro ai responsabili competenti per materia.</p><h3>Entro quanto tempo si può ottenere una risposta?</h3><p>La risposta verrà fornita mediamente entro 15 giorni. Il/La cittadino/a potrà ricevere una risposta intermedia non definitiva nel caso in cui la conclusione della “segnalazione” sia complessa e richieda più tempo.</p><h3>Che cosa significano i grafici presenti nella home del servizio on-line?</h3><p>I grafici sono la rappresentazione dei suggerimenti, segnalazioni e reclami raggruppati per aree tematiche.</p><p>Aree tematiche</p><ul><li>Ambiente</li><li>Anagrafe/Stato Civile</li><li>Attività produttive/Tributi</li><li>Cultura, Sport e Tempo Libero</li><li>Disabilità/Accessibilità</li><li>Edilizia pubblica/privata</li><li>Famiglia, Scuola, Giovani e Politiche sociali</li><li>Illuminazione pubblica, semafori</li><li>Manutenzione Stradale</li><li>Sicurezza Pubblica e Polizia Municipale</li><li>Viabilità e parcheggi</li><li>Altro</li></ul><p>I colori utilizzati</p><ul><li>Suggerimento=colore giallo</li><li>Segnalazione=colore azzurro</li><li>Reclamo=colore rosso</li><li>Chiuso=colore verde</li><li>In carico=colore giallo</li><li>Inviato=colore rosso</li></ul><h3>I suggerimenti, segnalazioni o reclami possono essere trasmessi solo online?</h3><p>No, i suggerimenti, le segnalazioni e i reclami possono essere presentati anche di persona presso i Centri Civici-Ufficio Relazioni con il Pubblico (U.R.P.), o in altra modalità. Tuttavia si consiglia l'utilizzo dell'apposito strumento SensorCivico.<br>L'Ente si impegna a rispondere nelle stesse modalità di inoltro a tutti i suggerimenti, segnalazioni e reclami.<br>La presentazione del reclamo non preclude il ricorso ad altri mezzi di tutela, amministrativi e/o giurisdizionali.</p><h3>SensorCivico è compatibile con dispositivi mobili (smartphone ecc..)?</h3><p>Sì, è possibile utilizzare l'applicazione con un'interfaccia semplificata e dedicata a smartphone o tablet.</p><h3>Come posso cancellare il mio profilo?</h3><p>Per cancellare il proprio profilo dalla lista degli utenti, è sufficiente inviare una e-mail (vedi contatti). In ogni momento è possibile riscriversi.</p>",
        'privacy'  => "<h3>Informativa ex art. 13, D.lgs 30 giugno 2003, n. 196</h3><p>La informiamo che i Suoi dati, siano essi personali, sensibili e/o giudiziari, sono trattati, con procedure informatizzate e manuali, nel rispetto delle disposizioni di tutela contenute nel Codice per la protezione dei dati personali, sia sotto il profilo procedimentale che della custodia atta a garantirne la riservatezza.</p><h3>Trattamento dei dati sensibili e/o giudiziari</h3><p>I dati da Lei conferiti, personali, sensibili e giudiziari sono trattati ai fini della gestione dello sportello online suggerimenti, segnalazioni, reclami SensorCivico e dei procedimenti.</p><h3>Conferimento dei dati</h3><p>Il conferimento dei dati ha natura obbligatoria limitatamente a nome, cognome ed e mail; il mancato conferimento comporta l’impossibilità di avvalersi del servizio online.</p><p>Il conferimento di ogni altro dato ha natura volontaria.</p><h3>Comunicazione e diffusione</h3><p>I dati che La riguardano sono comunicati nei limiti e con le procedure individuate dal Codice in materia di protezione dei dati personali, oltre che in evasione di eventuali compatibili istanze dei titolari di corrispondente diritto di accesso ai sensi di legge.</p><p>I dati possono essere comunicati tra URP e Centri Civici e uffici comunali competenti (di cui all'Allegato A del Regolamento organico e di organizzazione approvato nel testo vigente con deliberazione della Giunta Comunale n. 180 del 31.03.2014) ad evadere il reclamo, la segnalazione e a prendere nota del suggerimento; potranno essere altresì comunicati ai fini delle procedure di cui alla Carta dei Servizi di ciascun servizio, ove prevista.</p><p>I dati inoltre possono essere comunicati dal responsabile del trattamento ai suoi incaricati, agli incaricati dell’elaborazione di report statistici e all'amministrazione di sistema.</p><p>I dati verranno diffusi nelle sole forme aggregate ed anonime.</p><h3>Diritti dell’interessato</h3><p>L’articolo 7 del D.lgs. 30.6.2003, n. 196, “Codice in materia di protezione dei dati personali”, dispone che l’interessato ha diritto di:</p><ul> <li>ottenere la conferma dell’esistenza o meno di dati personali che lo riguardano, anche se non ancora registrati, e la loro comunicazione in forma intellegibile;</li> <li>conoscere l’origine dei dati personali, finalità e modalità del trattamento, logica applicata in caso di trattamento effettuato con l’ausilio di strumenti elettronici, gli estremi identificativi del titolare, e dei responsabili del trattamento dei dati.</li> <li>conoscere i soggetti o le categorie di soggetti ai quali i dati personali possono essere comunicati, anche in qualità di responsabili o incaricati;</li> <li>ottenere l’aggiornamento, la rettificazione ovvero, quando vi ha interesse, l’integrazione dei dati, la cancellazione, la trasformazione in forma anonima o il blocco dei dati trattati in violazione di legge, compresi quelli di cui non è necessaria la conservazione in relazione agli scopi per i quali i dati sono stati raccolti o successivamente trattati;</li> <li>opporsi, in tutto o in parte, per motivi legittimi, al trattamento dei dati personali che lo riguardano, ancorché pertinenti allo scopo della raccolta, o ai fini di invio di materiale pubblicitario o di vendita diretta o per il compimento di ricerche di mercato o comunicazione commerciale.</li></ul><h3>Titolare del trattamento</h3><p>Titolare del trattamento dei dati personali è [sitename].</p><h3>Responsabile del trattamento</h3><p>Responsabile del trattamento dei dati è [sitename]</p>",
        'terms'    => "<ol> <li><strong>Oggetto e definizioni</strong><ol><li>Le presenti clausole di utilizzo regolano e disciplinano l'utilizzo della piattaforma “SensorCivico” da parte dell'Utente, nonché le responsabilità di quest'ultimo relativamente all'utilizzo della piattaforma anzidetta.</li><li>Per “SensorCivico” si intende una piattaforma online tramite la quale l'Utente può inoltrare gratuitamente all'Ente suggerimenti, segnalazioni e reclami che possono essere georeferenziati per il miglioramento della qualità dei servizi offerti dall'Ente per migliorare la vivibilità del centro abitato. La piattaforma in riuso è di esclusiva proprietà del Consorzio dei Comuni Trentini (Consorzio) con sede in Trento, Via Torre Verde, 23 (P.IVA 01533550222).</li><li>Per “Utente” si intende la persona fisica o giuridica registrata ai sensi dell'art. 2 che effettua suggerimenti, o invia segnalazioni e reclami alla piattaforma “SensorCivico”.</li><li>Per “Amministratore” si intende il funzionario dell'Ente a cui sono indirizzati i suggerimenti, le segnalazioni e i reclami, e a cui compete la presa in carico e la risposta all'Utente.</li><li>Le presenti clausole di utilizzo si applicano a tutti i suggerimenti, le segnalazioni e i reclami comunque effettuati, anche tramite eventuali apposite applicazioni per smartphone o sistemi di terze parti integrate con il portale.</li><li>Di seguito l´attività di inoltro suggerimenti, segnalazioni e reclami verrà genericamente definita “attività di segnalazione”.</li></ol></li> <li><strong>Registrazione</strong><ol> <li>Per poter effettuare “l'attività di segnalazione” di cui all'articolo precedente, l'Utente deve necessariamente registrarsi tramite l'apposita procedura di registrazione.</li><li>L’Utente si impegna a fornire dati rispondenti al vero. Sono da considerarsi falsi i dati personali riconducibili a terzi.</li><li>L'Utente si assume ogni responsabilità civile e penale per l’eventuale falsità o non correttezza delle informazioni e dei dati comunicati. </li></ol></li> <li><strong>Attività di inoltro suggerimenti, segnalazioni e reclami</strong><ol><li>Oggetto dei suggerimenti, delle segnalazioni e dei reclami dell'Utente sono situazioni che possono contribuire al miglioramento della qualità dei servizi offerti dall'Ente e della vivibilità del centro abitato.</li><li>“L´attività di segnalazione” da parte dell'Utente potrà essere arricchita tramite l'invio informatico di fotografie e l’inserimento di commenti nel rispetto della normativa sulla privacy.</li> <li>“L´attività di segnalazione” dovrà riguardare esclusivamente situazioni attinenti l’attività e i servizi pubblici offerti dall'Ente</li><li>Con l'invio “delle segnalazioni” l'Utente dichiara di essere titolare di ogni diritto eventualmente connesso alla segnalazione (a titolo meramente esemplificativo e non esaustivo: le fotografie).</li><li>Il suggerimento, la segnalazione o il reclamo inseriti dall’Utente saranno visibili sulla piattaforma anche dagli Utenti non registrati, solo se l’Utente avrà scelto di renderli pubblici. L’Amministratore del sistema potrà in ogni momento eliminare dalla piattaforma il suggerimento, la segnalazione o il reclamo o oscurare parte del suggerimento, della segnalazione o del reclamo qualora essa/esso possa ledere il diritto di soggetti terzi o comunque contrasti con quanto previsto al punto 4.3.. L'Amministratore può, qualora ricorrano motivi di opportunità, rispondere all'Utente in forma privata tramite mail.</li><li>In ogni caso, la pubblicazione e l'eliminazione della segnalazione del suggerimento o del reclamo all'interno della piattaforma “SensorCivico” sono rimessi , per i motivi sopraindicati, alla valutazione dell’Amministratore del sistema.</li><li>Nel caso in cui il suggerimento, la segnalazione o il reclamo sia stato pubblicato sulla piattaforma “SensorCivico” su indicazione dell'Utente, è riservata allo stesso la possibilità di chiederne l'eliminazione via email in ogni momento e, a tal fine, l´Amministratore vi provvederà entro 5 giorni lavorativi.</li><li>Chiunque, previa registrazione, può inviare commenti su suggerimenti, segnalazioni, e reclami precedentemente pubblicati. Ai commenti non verrà data risposta da parte dell'Amministratore del sistema. L’Amministratore del sistema potrà altresì eliminare dalla piattaforma il commento qualora esso possa ledere il diritto di soggetti terzi o comunque contrasti con quanto previsto al punto 4.3.</li><li>I dati personali forniti dall’Utente registrato nell’ambito dell'“attività di segnalazione”, serviranno a fini esclusivamente statistici e verranno comunque trattati nel pieno rispetto della normativa vigente in materia di privacy (D.lgs. 196/2003). </li></ol></li> <li><strong>Responsabilità dell'Utente</strong><ol><li>L’Utente risponde direttamente secondo le leggi civili, penali e amministrative di tutti i contenuti caricati sulla piattaforma SensorCivico.</li><li>L'Utente si assume ogni responsabilità nonché ogni conseguenza diretta o indiretta derivanti da eventuali lesioni dei diritti di terzi (a titolo meramente esemplificativo e non esaustivo, diritti d’autore o altri diritti di proprietà, diritti relativi alla riservatezza delle persone etc.) dipendenti dall'inserimento nella segnalazione dell'Utente di testi, commenti, fotografie e/o qualsiasi altro materiale nella piattaforma.</li><li>L'Utente si impegna a non inserire nelle segnalazioni, nei suggerimenti o nei reclami materiale o estratti di materiale:<ul><li>coperti da diritto d'autore e di cui non sia esso stesso titolare; </li><li>che abbiano un contenuto diffamatorio o calunnioso; </li><li>contrari alla morale e all'ordine pubblico, ovvero in grado di turbare la quiete pubblica o privata o di recare offesa, o danno diretto o indiretto a chiunque o ad una specifica categoria di persone (per esempio è vietato l’inserimento di materiali o estratti di materiale che possano ledere la sensibilità di gruppi etnici o religiosi etc.); </li><li>contrari al diritto alla riservatezza di soggetti terzi; </li><li>lesivi dell'onore, del decoro o della reputazione di soggetti terzi; </li><li>comunque contrari alla legge.</li></ul></li></ol></li> <li><strong>Limitazioni di responsabilità</strong><ol><li>Il Consorzio e l’Amministratore non rispondono dei danni diretti, indiretti o consequenziali subiti dall'Utente o da terzi in dipendenza della pubblicazione della segnalazione e/o per danni di qualsiasi genere o a qualsiasi titolo connessi con dette situazioni e a tal fine l'Utente dichiara di manlevare il Consorzio e l’Amministratore da ogni forma di responsabilità.</li><li>Il Consorzio e l’Amministratore non sono responsabili per l’uso non opportuno dei dati di login, né per la loro diffusione.</li><li>Il Consorzio e l’Amministratore non sono responsabili per l’uso illegittimo che terzi possano fare del sito e della riproduzione totale o parziale dei contenuti.</li><li>Il materiale inviato dall'Utente non sarà restituito.</li></ol></li> <li><strong>Comunicazioni</strong><ol><li>L'Utente prende atto ed accetta che tutte le eventuali comunicazioni, notifiche, informazioni e comunque ogni documentazione relativa alle operazioni eseguite e riferite all'utilizzo della piattaforma “SensorCivico” vengano inviate all'indirizzo di posta elettronica indicato dall'Utente durante la procedura di registrazione.</li></ol></li></ol>",
        'cookie'   => "<p>Nessun dato personale degli utenti viene in proposito acquisito dal sito.</p><p>Non viene fatto uso di cookies per la trasmissione di informazioni di carattere personale, né vengono utilizzati c.d. cookies persistenti di alcun tipo, ovvero sistemi per il tracciamento degli utenti.</p><p>L'uso di c.d. cookies di sessione (che non vengono memorizzati in modo persistente sul computer dell'utente e svaniscono con la chiusura del browser) è strettamente limitato alla trasmissione di identificativi di sessione (costituiti da numeri casuali generati dal server) necessari per consentire l'esplorazione sicura ed efficiente del sito.</p><p>I c.d. cookies di sessione utilizzati in questo sito evitano il ricorso ad altre tecniche informatiche potenzialmente pregiudizievoli per la riservatezza della navigazione degli utenti e non consentono l'acquisizione di dati personali identificativi dell'utente.</p>",
        'footer'   => "Da compilare",
        'contacts' => "Da compilare"
    );

    public function setScriptOptions( eZScript $script )
    {
        return $script->getOptions(
            '[parent-node:][step:][sa_suffix:][clean]',
            '',
            array(
                'parent-node' => 'Nodo id contenitore di sensor (Applicazioni di default)',
                'step' => 'Esegue solo lo step selezionato: gli step possibili sono' . implode( ', ', $this->steps ),
                'sa_suffix' => 'Suffisso del siteaccess (default: sensorcivico)',
                'clean' => 'Elimina tutti i contenuti presenti di sensor prima di eseguire l\'installazione'
            )
        );
    }

    public function beforeInstall( $options = array() )
    {        
        eZContentClass::removeTemporary();
        $this->options = $options;

        if ( !isset( $this->options['sa_suffix'] ) )
        {
            $this->options['sa_suffix'] = 'sensorcivico';
        }

        if ( isset( $this->options['step'] ) )
        {
            if ( array_key_exists( $this->options['step'], $this->steps ) )
                $this->installOnlyStep = $this->options['step'];
            else
                throw new Exception( "Step {$this->options['step']} not found, run script with -h for help" );

            if ( isset( $this->options['clean'] ) )
            {
                throw new Exception( "Can not activate 'clean' with 'step' option" );
            }
        }

        if ( isset( $this->options['clean'] ) )
        {
            self::cleanup();
        }
    }

    protected static function cleanup()
    {
        OpenPALog::warning( "Cleanup data" );
        $rootNode = ObjectHandlerServiceControlSensor::rootNode();
        if ( $rootNode instanceof eZContentObjectTreeNode )
        {
            eZContentObjectTreeNode::removeNode( $rootNode->attribute( 'node_id' ) );
        }
        unset( $GLOBALS['SensorRootNode'] );
        eZCollaborationItem::cleanup();
    }


    public function install()
    {
        OpenPALog::warning( "Controllo stati" );
        $states = self::installStates();
        
        OpenPALog::warning( "Controllo sezioni" );
        $section = self::installSections();

        OpenPALog::warning( "Controllo classi" );
        self::installClasses();

        OpenPALog::warning( "Installazione Sensor root" );
        if ( isset( $this->options['parent-node'] ) ) {
            $parentNodeId = $this->options['parent-node'];
        }
        else
        {
            $parentNodeId = OpenPAAppSectionHelper::instance()->rootNode()->attribute('node_id');
        }
        $root = self::installAppRoot( $parentNodeId, $section, $this->options );

        if ( $this->installOnlyStep !== null )
        {
            OpenPALog::warning( "Install step " . $this->steps[$this->installOnlyStep] );
        }

        if ( ( $this->installOnlyStep !== null && $this->installOnlyStep == 'a' ) || $this->installOnlyStep === null )
        {
            OpenPALog::warning( "Installazione Sensor segnalazioni" );
            self::installSensorPostStuff( $root, $section, $this->installOnlyStep === null );
        }

        if ( ( $this->installOnlyStep !== null && $this->installOnlyStep == 'r' ) || $this->installOnlyStep === null )
        {
            OpenPALog::warning( "Installazione ruoli" );
            self::installRoles( $section, $states );
        }

        if ( ( $this->installOnlyStep !== null && $this->installOnlyStep == 'c' ) || $this->installOnlyStep === null )
        {
            OpenPALog::warning( 'Salvo configurazioni' );
            self::installIniParams( $this->options['sa_suffix'] );
        }

        eZCache::clearById( 'global_ini' );
        eZCache::clearById( 'template' );

        OpenPALog::error( "@todo Impostare i workflow di PostPublish e di PreDelete" );
    }

    public function afterInstall()
    {
        return false;
    }

    protected static function installStates()
    {
        $sensorStates = OpenPABase::initStateGroup(
            ObjectHandlerServiceControlSensor::$stateGroupIdentifier,
            ObjectHandlerServiceControlSensor::$stateIdentifiers
        );

        $privacyStates = OpenPABase::initStateGroup(
            ObjectHandlerServiceControlSensor::$privacyStateGroupIdentifier,
            ObjectHandlerServiceControlSensor::$privacyStateIdentifiers
        );
        
        $moderationStates = OpenPABase::initStateGroup(
            ObjectHandlerServiceControlSensor::$moderationStateGroupIdentifier,
            ObjectHandlerServiceControlSensor::$moderationStateIdentifiers
        );
        return array_merge( $sensorStates, $privacyStates, $moderationStates );
    }

    protected static function installSections()
    {
        $section = OpenPABase::initSection(
            ObjectHandlerServiceControlSensor::SECTION_NAME,
            ObjectHandlerServiceControlSensor::SECTION_IDENTIFIER,
            OpenPAAppSectionHelper::NAVIGATION_IDENTIFIER
        );
        return $section;
    }

    public static function sensorClassIdentifiers()
    {
        return array(
            "sensor_root",
            "sensor_area",
            "sensor_category",
            "sensor_operator",
            "sensor_post",
            "sensor_post_root"
        );
    }

    public static function sensorPostCategories()
    {

        //Todo: get a repo, in caso di problemi fallback su array
        return array(
            "Ambiente" => array(
                "Parchi, giardini e aree gioco", "Potatura e cura del verde pubblico", "Panchine-Parchi", "Animali in città (Deiezioni e altro)", "Rifiuti", "Aria", "Rumore ed elettromagnetismo"
            ),
            "Anagrafe/Stato Civile" => array(
                "Servizi demografici/Stato civile"
            ),
            "Attività produttive/Tributi" => array(
                "Commercio", "Pubblici esercizi", "Rumore - Pubblici esercizi", "Tributi"
            ),
            "Cultura, Sport e Tempo Libero" => array(
                "Cultura/Eventi e spettacoli", "Biblioteche", "Servizi Museali", "Impianti sportivi"
            ),
            "Disabilità/Accessibilità" => array(
                "Disabilità/Barriere architettoniche"
            ),
            "Edilizia pubblica/privata" => array(
                "Edlilizia scolastica", "Edilizia privata - cantieri", "Controllo costruzioni", "Edilizia pubblica - edilizia abitativa", "Edilizia pubblica - opere pubbliche edifici"
            ),
            "Famiglia, Scuola, Giovani e Politiche sociali" => array(
                "Giovani", "Servizi cimiteriali", "Scuole materne", "Servizi scolastici: refezione scolastiche", "Senior - Silver Card", "Senior - Soggiorni anziani"
            ),
            "Illuminazione pubblica, semafori" => array(
                "Illuminazione pubblica", "Semafori-manutenzione", "Seganletica-manutenzione", "Posizionamento cassonetti"
            ),
            "Manutenzione Stradale" => array(
                "Strade", "Marciapiedi", "Tombini", "Ciclabili-manutenzione", "Dossi-manutenzione"
            ),
            "Sicurezza Pubblica e Polizia Municipale" => array(
                "Contravvenzioni", "Controllo urbano", "Degrado urbano/Vandalismo"
            ),
            "Viabilità e parcheggi" => array(
                "Viabilità", "Zone colorate/Bollini", "Parcheggi/Soste selvagge", "Dossi-viabilità", "Ciclabili-viabilità", "Segnaletica-viabilità", "Specchi parabolici", "Semafori-viabilità"
            ),
            "Altro"
        );
    }
    
    protected static function installClasses()
    {
        OpenPAClassTools::installClasses( OpenPASensorInstaller::sensorClassIdentifiers() );
    }

    protected static function installAppRoot( $parentNodeId, eZSection $section, $options = array() )
    {
        $rootObject = eZContentObject::fetchByRemoteID( ObjectHandlerServiceControlSensor::sensorRootRemoteId() );
        if ( !$rootObject instanceof eZContentObject )
        {

            // root
            $params = array(
                'parent_node_id' => $parentNodeId,
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => ObjectHandlerServiceControlSensor::sensorRootRemoteId(),
                'class_identifier' => 'sensor_root',
                'attributes' => array(
                    'name' => 'SensorCittà',
                    'logo' => 'extension/openpa_sensor/doc/default/logo.png',
                    'logo_title' => 'Sensor[Città]',
                    'logo_subtitle' => 'Il Comune in ascolto',
                    'banner' => 'extension/openpa_sensor/doc/default/banner.png',
                    'banner_title' => "[Sensor]Civico: la [tua voce] conta",
                    'banner_subtitle' => "Aiutaci a migliorare: [insieme] è meglio",
                    'faq' => SQLIContentUtils::getRichContent( OpenPASensorInstaller::prepareTextContent( 'faq', $options['sa_suffix'] ) ),
                    'privacy' => SQLIContentUtils::getRichContent( OpenPASensorInstaller::prepareTextContent( 'privacy', $options['sa_suffix'] ) ),
                    'terms' => SQLIContentUtils::getRichContent( OpenPASensorInstaller::prepareTextContent( 'terms', $options['sa_suffix'] ) ),
                    'footer' => SQLIContentUtils::getRichContent( OpenPASensorInstaller::prepareTextContent( 'footer', $options['sa_suffix'] ) ),
                    'contacts' => SQLIContentUtils::getRichContent( OpenPASensorInstaller::prepareTextContent( 'contacts', $options['sa_suffix'] ) ),
                    'forum_enabled' => isset( $options['forum'] ),
                    'survey_enabled' => isset( $options['survey'] ),
                    'post_enabled' => isset( $options['post'] )
                )
            );
            /** @var eZContentObject $rootObject */
            $rootObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$rootObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Sensor root node' );
            }
        }
        return $rootObject;
    }

    protected static function installSensorPostStuff( eZContentObject $rootObject, eZSection $section, $installDemoContent = true )
    {
        $containerObject = eZContentObject::fetchByRemoteID( ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_postcontainer' );
        if ( !$containerObject instanceof eZContentObject )
        {
            // Post container
            OpenPALog::warning( "Install Post container" );
            $params = array(
                'parent_node_id' => $rootObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_postcontainer',
                'class_identifier' => 'sensor_post_root',
                'attributes' => array(
                    'name' => 'Segnala!',
                    'short_description' => SQLIContentUtils::getRichContent( "<p>Attraverso la piattaforma SensorCivico i cittadini possono formulare suggerimenti, segnalazioni e reclami su mappa per il miglioramento della qualità dei servizi offerti e la vivibilità del nucleo abitato.</p>" ),
                    'description' => SQLIContentUtils::getRichContent( "<p>Attraverso la <b>piattaforma SensorCivico</b> i/le cittadini/e possono formulare suggerimenti, segnalazioni e reclami su mappa (OpenStreet map) per il miglioramento della qualità dei servizi offerti e la vivibilità del nucleo abitato.<br>Tutti i suggerimenti, segnalazioni e reclami saranno resi pubblici a meno che il/la cittadino/a non abbia indicato diversamente in fase di caricamento della “segnalazione”.</p>" ),
                    'image' => 'extension/openpa_sensor/doc/default/sensor_post_root.png'
                )
            );
            /** @var eZContentObject $containerObject */
            $containerObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$containerObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Sensor container node' );
            }
        }
        if ( $containerObject->attribute( 'class_identifier' ) == 'folder' )
        {
            $mapping = array(
                "name" => "name",
                "short_description" => "short_description",
                "description" => "description",
                "image" => ""
            );

            $conversionFunctions = new conversionFunctions();
            $containerObject = $conversionFunctions->convertObject( $containerObject->attribute('id'), eZContentClass::classIDByIdentifier( 'sensor_post_root' ), $mapping );
            if ( !$containerObject )
            {
                throw new Exception( "Errore nella conversione dell'oggetto contentitore" );
            }
        }

        $groupObject = eZContentObject::fetchByRemoteID( ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_operators' );
        if ( !$groupObject instanceof eZContentObject )
        {
            // Operator group
            OpenPALog::warning( "Install Operators group" );
            $params = array(
                'parent_node_id' => $rootObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_operators',
                'class_identifier' => 'user_group',
                'attributes' => array(
                    'name' => 'Operatori'
                )
            );
            /** @var eZContentObject $groupObject */
            $groupObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$groupObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Sensor group node' );
            }
        }

        if ( $groupObject->attribute( 'main_node' )->attribute( 'children_count' ) > 0 )
        {
            $installDemoContent = false;
        }

        if ( $installDemoContent )
        {
            // Operator sample
            OpenPALog::warning( "Install Operator demo as main operator" );
            $params = array(
                'parent_node_id' => $groupObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'class_identifier' => 'sensor_operator',
                'attributes' => array(
                    'name' => 'Responsabile URP'
                )
            );
            /** @var eZContentObject $categoryObject */
            $operatorObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$operatorObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Sensor operator node' );
            }

            // Area container
            OpenPALog::warning( "Install Area demo as container" );
            $params = array(
                'parent_node_id' => $rootObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'class_identifier' => 'sensor_area',
                'attributes' => array(
                    'name' => eZINI::instance()->variable( 'SiteSettings', 'SiteName' ),
                    'approver' => $operatorObject->attribute( 'id' ),
                    'geo' => '1|#46.0700915|#11.119762600000058|#'
                )
            );
            /** @var eZContentObject $areaObject */
            $areaObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$areaObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Sensor area node' );
            }
        }
        $categoriesObject = eZContentObject::fetchByRemoteID( ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_postcategories' );
        if ( !$categoriesObject instanceof eZContentObject )
        {
            // Categories container
            OpenPALog::warning( "Install Category container" );
            $params = array(
                'parent_node_id' => $rootObject->attribute( 'main_node_id' ),
                'section_id' => $section->attribute( 'id' ),
                'remote_id' => ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_postcategories',
                'class_identifier' => 'folder',
                'attributes' => array(
                    'name' => 'Categorie'
                )
            );
            /** @var eZContentObject $categoriesObject */
            $categoriesObject = eZContentFunctions::createAndPublishObject( $params );
            if( !$categoriesObject instanceof eZContentObject )
            {
                throw new Exception( 'Failed creating Sensor categories node' );
            }
        }


        if ($installDemoContent )
        {
            OpenPALog::warning( "Install Categories" );
            $categories = OpenPASensorInstaller::sensorPostCategories();
            OpenPASensorInstaller::installPostCategories($categories, $categoriesObject->attribute( 'main_node_id' ), $section->attribute( 'id' ));
        }
    }

    /**
     * @param $categories
     * @param $parentNodeID
     * @param $sectionID
     * @throws Exception
     */
    protected static function installPostCategories( $categories, $parentNodeID, $sectionID )
    {
        foreach ($categories as $k => $v )
        {
            if (is_array($v))
            {
                $pId = OpenPASensorInstaller::installPostCategory($k, $parentNodeID, $sectionID);
                OpenPASensorInstaller::installPostCategories($v, $pId, $sectionID);
            }
            else
            {
                OpenPASensorInstaller::installPostCategory($v, $parentNodeID, $sectionID);
            }
        }
    }

    /**
     * @param $category
     * @param $parentNodeID
     * @param $sectionID
     * @return Int
     * @throws Exception
     */
    protected static function installPostCategory( $category, $parentNodeID, $sectionID )
    {
        OpenPALog::warning( "Install Category " .  $category  );
        $params = array(
            'parent_node_id'   => $parentNodeID,
            'section_id'       => $sectionID,
            'remote_id'        => md5( $category ),
            'class_identifier' => 'sensor_category',
            'attributes'       => array(
                'name' => $category
            )
        );
        /** @var eZContentObject $categoryObject */
        $categoryObject = eZContentFunctions::createAndPublishObject( $params );
        if( !$categoryObject instanceof eZContentObject )
        {
            throw new Exception( 'Failed creating Sensor category node - ' . $category );
        }
        else
        {
            return $categoryObject->attribute( 'main_node_id' );
        }
    }

    /**
     * @param $type
     * @param $saSuffix
     * @return mixed
     */
    protected static function prepareTextContent( $type, $saSuffix )
    {
        $frontendSiteUrl = eZINI::instance()->variable( 'SiteSettings', 'SiteURL' );
        $parts = explode( '/', $frontendSiteUrl );
        $siteAccess = array_pop($parts);
        $frontendSiteUrl = implode('/', $parts);

        $search  = array(
            '[privacy-link]',
            '[sitename]'
        );
        $replace = array(
            $frontendSiteUrl . '/' . $saSuffix . '/sensor/privacy',
            eZINI::instance()->variable( 'SiteSettings', 'SiteName' )

        );
        return str_replace($search, $replace, OpenPASensorInstaller::$textContent[$type]);
    }

    protected static function installRoles( eZSection $section, array $states )
    {
        $roles = array(

            "Sensor Admin" => array(
                array(
                    'ModuleName' => 'apps',
                    'FunctionName' => '*'
                ),
                array(
                    'ModuleName' => 'openpa',
                    'FunctionName' => '*'
                ),

                array(
                    'ModuleName' => 'sensor',
                    'FunctionName' => '*'
                ),
                array(
                    'ModuleName' => 'user',
                    'FunctionName' => 'login',
                    'Limitation' => array(
                        'SiteAccess' => eZSys::ezcrc32( OpenPABase::getCustomSiteaccessName( 'sensor', false ) )
                    )
                ),
                array(
                    'ModuleName' => 'websitetoolbar',
                    'FunctionName' => '*'
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'edit',
                    'Limitation' => array( 'Section' => $section->attribute( 'id' ) )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array( 'Section' => $section->attribute( 'id' ) )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'remove',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'sensor_post' )
                        ),
                        'Section' => $section->attribute( 'id' )
                    )
                )
            ),

            "Sensor Operators" => array(
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array( 'Class' => eZContentClass::classIDByIdentifier( 'dipendente' ) )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'sensor_area' ),
                            eZContentClass::classIDByIdentifier( 'sensor_operator' )
                        ),
                        'Section' => $section->attribute( 'id' ) )
                ),
                array(
                    'ModuleName' => 'notification',
                    'FunctionName' => '*'
                ),
                array(
                    'ModuleName' => 'sensor',
                    'FunctionName' => 'manage'
                ),
            ),

            "Sensor Reporter" => array(
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'create',
                    'Limitation' => array(
                        'Class' => eZContentClass::classIDByIdentifier( 'sensor_post' ),
                        'ParentClass' => eZContentClass::classIDByIdentifier( 'sensor_post_root' ),
                        'Section' => $section->attribute( 'id' )
                    )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'sensor_post' )
                        ),
                        'Owner' => 1,
                        'Section' => $section->attribute( 'id' )
                    )
                ),
                array(
                    'ModuleName' => 'notification',
                    'FunctionName' => '*'
                ),
                array(
                    'ModuleName' => 'user',
                    'FunctionName' => 'login',
                    'Limitation' => array(
                        'SiteAccess' => eZSys::ezcrc32( OpenPABase::getCustomSiteaccessName( 'sensor', false ) )
                    )
                ),
                array(
                    'ModuleName' => 'collaboration',
                    'FunctionName' => '*'
                ),
            ),
            
            "Sensor Assistant" => array(
                array(
                    'ModuleName' => 'sensor',
                    'FunctionName' => 'behalf'
                )
            ),

            "Sensor Anonymous" => array(
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array(
                        'Class' => eZContentClass::classIDByIdentifier( 'sensor_post' ),
                        'Section' => $section->attribute( 'id' ),
                        'StateGroup_privacy' => $states['privacy.public']->attribute( 'id' ),
                        'StateGroup_moderation' => array(
                            $states['moderation.skipped']->attribute( 'id' ),
                            $states['moderation.accepted']->attribute( 'id' )
                        )
                    )
                ),
                array(
                    'ModuleName' => 'content',
                    'FunctionName' => 'read',
                    'Limitation' => array(
                        'Class' => array(
                            eZContentClass::classIDByIdentifier( 'sensor_area' ),
                            eZContentClass::classIDByIdentifier( 'sensor_post_root' ),
                            eZContentClass::classIDByIdentifier( 'folder' )
                        ),
                        'Section' => $section->attribute( 'id' )
                    )
                ),
                array(
                    'ModuleName' => 'sensor',
                    'FunctionName' => 'use'
                ),
                array(
                    'ModuleName' => 'user',
                    'FunctionName' => 'login',
                    'Limitation' => array(
                        'SiteAccess' => eZSys::ezcrc32( OpenPABase::getCustomSiteaccessName( 'sensor', false ) )
                    )
                ),
            )
        );

        foreach( $roles as $roleName => $policies )
        {
            OpenPABase::initRole( $roleName, $policies, true );
        }

        $anonymousUserId = eZINI::instance()->variable( 'UserSettings', 'AnonymousUserID' );
        /** @var eZRole $anonymousRole */
        $anonymousRole = eZRole::fetchByName( "Sensor Anonymous" );
        if ( !$anonymousRole instanceof eZRole )
        {
            throw new Exception( "Error: problem with roles" );
        }
        $anonymousRole->assignToUser( $anonymousUserId );

        /** @var eZRole $reporterRole */
        $reporterRole = eZRole::fetchByName( "Sensor Reporter" );
        if ( !$reporterRole instanceof eZRole )
        {
            throw new Exception( "Error: problem with roles" );
        }
        $memberNodeId = eZINI::instance()->variable( 'UserSettings', 'DefaultUserPlacement' );
        $members = eZContentObject::fetchByNodeID( $memberNodeId );
        if ( $members instanceof eZContentObject )
        {
            $anonymousRole->assignToUser( $members->attribute( 'id' ) );
            $reporterRole->assignToUser( $members->attribute( 'id' ) );
        }

        $groupObject = eZContentObject::fetchByRemoteID( ObjectHandlerServiceControlSensor::sensorRootRemoteId() . '_operators' );
        /** @var eZRole $operatorRole */
        $operatorRole = eZRole::fetchByName( "Sensor Operators" );
        if ( !$operatorRole instanceof eZRole )
        {
            throw new Exception( "Error: problem with roles" );
        }
        $anonymousRole->assignToUser( $groupObject->attribute( 'id' ) );
        $reporterRole->assignToUser( $groupObject->attribute( 'id' ) );
        $operatorRole->assignToUser( $groupObject->attribute( 'id' ) );

    }

    protected static function installIniParams( $saSuffix )
    {
        $sensor = OpenPABase::getCustomSiteaccessName( 'sensor', false );
        $sensorPath = "settings/siteaccess/{$sensor}/";

        // impostatzioni in backend
        $backend = OpenPABase::getBackendSiteaccessName();
        $backendPath = "settings/siteaccess/{$backend}/";
        $iniFile = "contentstructuremenu.ini";
        $ini = new eZINI( $iniFile . '.append', $backendPath, null, null, null, true, true );
        $value = array_unique( array_merge( (array) $ini->variable( 'TreeMenu', 'ShowClasses' ), array( 'sensor_root', 'sensor_post_root', 'sensor_area', 'sensor_category' ) ) );
        $ini->setVariable( 'TreeMenu', 'ShowClasses', $value );
        if ( !$ini->save() ) throw new Exception( "Non riesco a salvare {$backendPath}{$iniFile}" );

        $iniFile = "site.ini";
        $ini = new eZINI( $iniFile . '.append', $backendPath, null, null, null, true, true );
        $value = array_unique(
            array_merge(
                (array) $ini->variable( 'ExtensionSettings', 'ActiveAccessExtensions' ),
                array(
                    'ocoperatorscollection',
                    'ocsocialuser',
                    'ocsocialdesign',
                    'ocsensor',
                    'openpa_sensor'
                )
            )
        );
        $ini->setVariable( 'ExtensionSettings', 'ActiveAccessExtensions', $value );
        $relatedSiteAccessList = array_unique( array_merge( (array) $ini->variable( 'SiteAccessSettings', 'RelatedSiteAccessList' ), array( $sensor ) ) );
        $ini->setVariable( 'SiteAccessSettings', 'RelatedSiteAccessList', $relatedSiteAccessList );
        if ( !$ini->save() ) throw new Exception( "Non riesco a salvare {$backendPath}{$iniFile}" );

        // impostatzioni in sensor
        eZDir::mkdir( $sensorPath );

        $frontend = OpenPABase::getFrontendSiteaccessName();
        $frontendPath = "settings/siteaccess/{$frontend}/";
        // Se la siteurl contiene http:// genera una stringa sbagliata
        //$frontendSiteUrl = eZINI::instance()->variable( 'SiteSettings', 'SiteURL' );
        //$parts = explode( '/', $frontendSiteUrl ); //bugfix
        //$frontendSiteUrl = $parts[0];
        $frontendSiteUrl = eZINI::instance()->variable( 'SiteSettings', 'SiteURL' );
        $parts = explode( '/', $frontendSiteUrl );
        $siteAccess = array_pop($parts);
        $frontendSiteUrl = implode('/', $parts);

        eZFileHandler::copy( $frontendPath . 'site.ini.append.php', $sensorPath . 'site.ini.append.php' );
        $iniFile = "site.ini";
        $ini = new eZINI( $iniFile . '.append', $sensorPath, null, null, null, true, true );
        $ini->setVariable(
            'ExtensionSettings',
            'ActiveAccessExtensions',
            array(                
                'openpa_theme_2014',
                'ocbootstrap',
                'ocoperatorscollection',
                'ocsocialuser',
                'ocsocialdesign',
                'ocsensor',
                'openpa_sensor'
            )
        );
        $ini->setVariable( 'SiteSettings', 'SiteURL', $frontendSiteUrl . '/' . $saSuffix );
        $ini->setVariable( 'SiteSettings', 'DefaultPage', 'sensor/home' );
        $ini->setVariable( 'SiteSettings', 'IndexPage', 'sensor/home' );
        $ini->setVariable( 'SiteSettings', 'LoginPage', 'embedded' );
        $ini->setVariable( 'SiteAccessSettings', 'RelatedSiteAccessList', $relatedSiteAccessList );
        $ini->setVariable( 'DesignSettings', 'SiteDesign', 'sensor' );
        $ini->setVariable( 'DesignSettings', 'AdditionalSiteDesignList', array( '', 'social', 'ocbootstrap', 'standard' ) );
        if ( !$ini->save() ) throw new Exception( "Non riesco a salvare {$sensorPath}{$iniFile}" );

        $iniFile = "ezcomments.ini";
        $ini = new eZINI( $iniFile . '.append', $sensorPath, null, null, null, true, true );
        $ini->setVariable( 'RecaptchaSetting', 'PublicKey', '6Lee6v4SAAAAAKaBcnKYaMiD' );
        $ini->setVariable( 'RecaptchaSetting', 'PrivateKey', '6Lee6v4SAAAAAD39ImIzsTrIOkyPy2La13T7aZzf' );
        $ini->setVariable( 'RecaptchaSetting', 'Theme', 'custom' );
        $ini->setVariable( 'RecaptchaSetting', 'Language', 'en' );
        $ini->setVariable( 'RecaptchaSetting', 'TabIndex', '0' );
        if ( !$ini->save() ) throw new Exception( "Non riesco a salvare {$sensorPath}{$iniFile}" );

        OpenPALog::error( "@todo Aggiungere siteaccess in override/site.ini:
[SiteSettings]
SiteList[]={$sensor}
[SiteAccessSettings]
AvailableSiteAccessList[]={$sensor}
HostUriMatchMapItems[]={$frontendSiteUrl};{$saSuffix};{$sensor} \n" );
    }
}
