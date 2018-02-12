<?php

/* DELETE THIS FILE FROM YOUR WEBSERVER AFTER YOUR TABLES HAVE BEEN CREATED */

include('ITpings_configuration.php');

/*  ================================================================================================

    Create Database Schema with tables:
                                        applications
                                        devices
                                        gateways
                                        pings
                                        pingedgateways
                                        sensors
                                        sensorvalues
                                        POSTrequests
                                        events
*/

function create_Table($table, $idfield, $fields, $foreignkeys)
{
    add_QueryLog("<h2>Create Table: <b>$table</b></h2>");

    $sql = "CREATE TABLE IF NOT EXISTS $table (";
    if ($idfield) {
        $sql .= "$idfield INT(10) UNSIGNED NOT NULL UNIQUE AUTO_INCREMENT COMMENT 'ITpings Primary Key',";
    }
    foreach ($fields as $field) {
        $sql .= "$field[0] $field[1] COMMENT '$field[2]',";
    }
    if (USE_REFERENTIAL_INTEGRITY) {
        foreach ($foreignkeys as $key) {
            $sql .= "	FOREIGN KEY ($key[0]) $key[1],";
        }
    }
    $sql .= "PRIMARY KEY ($idfield)";
    $sql .= ")";
    $sql .= " ENGINE=InnoDB DEFAULT CHARSET=utf8";
    $sql .= ";";
    SQL_Query($sql);
}

//region====================================================================================   create all Tables
function create_ITpings_Tables_in_Database()
{

    function OnKey($str)
    {// Make Foreign Key definitions better readable
        return "(" . $str . ")";
    }

    create_Table(TABLE_POSTREQUESTS, PRIMARYKEY_POSTrequests
        , [//Fields
            [ITPINGS_POST_body
                , TYPE_POST_BODY
                , "Bare POST body"]
        ]
        , NO_FOREIGNKEYS
    );

    create_Table(TABLE_APPLICATIONS, PRIMARYKEY_Application
        , [//Fields
            [TTN_app_id
                , TYPE_TTN_APP_ID
                , "TTN Application ID"]
            , [ITPINGS_DESCRIPTION
                , TYPE_TTN_APP_DESCRIPTION
                , "Description "]
        ]
        , NO_FOREIGNKEYS
    );

    create_Table(TABLE_DEVICES, PRIMARYKEY_Device
        , [//Fields
            [TTN_dev_id
                , TYPE_TTN_DEVICE_ID
                , "TTN Device ID"]
            , [TTN_hardware_serial
                , TYPE_TTN_HARDWARE_SERIAL
                , "TTN Application ID"]
        ], NO_FOREIGNKEYS
    );

    create_Table(TABLE_APPLICATIONDEVICES, PRIMARYKEY_ApplicationDevice
        , [//Fields
            [PRIMARYKEY_Application
                , TYPE_FOREIGNKEY
                , ITpings_PrimaryKey_In_Table . TABLE_APPLICATIONS]
            , [PRIMARYKEY_Device
                , TYPE_FOREIGNKEY
                , ITpings_PrimaryKey_In_Table . TABLE_DEVICES]
        ]
        , [
            [PRIMARYKEY_Application
                , IS_A_FOREIGNKEY_IN . TABLE_APPLICATIONS . OnKey(PRIMARYKEY_Application)]

            , [PRIMARYKEY_Device
                , IS_A_FOREIGNKEY_IN . TABLE_DEVICES . OnKey(PRIMARYKEY_Device)]
        ]
    );

    create_Table(TABLE_GATEWAYS, PRIMARYKEY_Gateway
        , [//Fields
            [TTN_gtw_id
                , TYPE_TTN_GTW_ID
                , "TTN Gateway ID"]
            , [TTN_gtw_trusted
                , TYPE_TTN_TRUSTED_GTW
                , "TTN Gateway Trusted"]
            , [ITPINGS_LATITUDE
                , LATITUDE_ACCURACY
                , "TTN Gateway Latitude"]
            , [ITPINGS_LONGITUDE
                , LONGITUDE_ACCURACY
                , "TTN Gateway Longitude"]
            , [ITPINGS_ALTITUDE
                , ALTITUDE_ACCURACY
                , "TTN Gateway Altitude"]
            , [ITPINGS_LOCATIONSOURCE
                , TYPE_LOCATION_SOURCE
                , "TTN (registry)"]
        ]
        , NO_FOREIGNKEYS
    );

//    create_Table(TABLE_FREQUENCIES, PRIMARYKEY_Frequency
//        , [//Fields
//            [TTN_frequency, TYPE_TTN_FREQUENCY, "TTN Frequency"]
//        ]
//        , NO_FOREIGNKEYS
//    );

    create_Table(TABLE_PINGS, PRIMARYKEY_Ping
        , [//Fields
            [ITPINGS_CREATED_TIMESTAMP
                , " TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP "
                , "Time Ping was Created in ITpings database"]
            , [PRIMARYKEY_ApplicationDevice
                , TYPE_FOREIGNKEY
                , ITpings_PrimaryKey_In_Table . TABLE_APPLICATIONDEVICES]
            , [TTN_port
                , TYPE_TTN_PORT
                , "TTN port number"]
            , [TTN_counter
                , TYPE_TTN_FRAME_COUNTER
                , "TTN Frame Counter"]
            , [TTN_downlink_url
                , TYPE_TTN_DOWNLINK
                , "TTN Downlink URI"]
            , [TTN_payload_raw
                , TYPE_TTN_PAYLOAD_RAW
                , "TTN Payload Raw format"]

            , [ITPINGS_TIME
                , TYPE_TTN_TIMESTRING
                , "TTN time"]

            //TODO create lookup tables to reduce the size of pings Table by 16 bytes for each entry
            , [ITPINGS_FREQUENCY
                , TYPE_TTN_FREQUENCY
                , "TTN frequency"]//5 bytes save 4
            , [ITPINGS_MODULATION
                , TYPE_TTN_MODULATION
                , "TTN modulation"]//4 save 3
            , [ITPINGS_DATA_RATE
                , TYPE_TTN_DATA_RATE
                , "TTN data rate"]//8 save 7
            , [ITPINGS_CODING_RATE
                , TYPE_TTN_CODING_RATE
                , "TTN coding rate"]//3 save 2

            , [ITPINGS_LATITUDE
                , LATITUDE_ACCURACY
                , "TTN Ping Latitude"]
            , [ITPINGS_LONGITUDE
                , LONGITUDE_ACCURACY
                , "TTN Ping Longitude"]
            , [ITPINGS_ALTITUDE
                , ALTITUDE_ACCURACY
                , "TTN Ping Altitude"]
            , [ITPINGS_LOCATIONSOURCE
                , TYPE_LOCATION_SOURCE
                , "TTN (registry)"]
        ]
        , [
            [PRIMARYKEY_ApplicationDevice
                , IS_A_FOREIGNKEY_IN . TABLE_APPLICATIONDEVICES . OnKey(PRIMARYKEY_ApplicationDevice)]
        ]
    );

    create_Table(TABLE_PINGEDGATEWAYS, PRIMARYKEY_PingedGateway

        , [//Fields
            [PRIMARYKEY_Ping
                , TYPE_FOREIGNKEY
                , ITpings_PrimaryKey_In_Table . TABLE_PINGS]
            , [PRIMARYKEY_Gateway
                , TYPE_FOREIGNKEY
                , ITpings_PrimaryKey_In_Table . TABLE_GATEWAYS]
            , [TTN_timestamp
                , TYPE_TYPE_TIMESTAMP
                , "TTN GatewayPing Timestamp"]
            , [TTN_time
                , TYPE_TTN_TIMESTRING
                , "TTN GatewayPing Time"]
            , [TTN_channel
                , TYPE_TTN_CHANNEL
                , "TTN GatewayPing Channel"]
            , [TTN_rssi
                , TYPE_TTN_RSSI
                , "TTN GatewayPing RSSI"]
            , [TTN_snr
                , TYPE_TTN_SNR
                , "TTN GatewayPing SNR"]
            , [TTN_rf_chain
                , TYPE_TTN_RFCHAIN
                , "TTN GatewayPing RFChain"]
        ]
        , [
            [PRIMARYKEY_Ping
                , IS_A_FOREIGNKEY_IN . TABLE_PINGS . OnKey(PRIMARYKEY_Ping)]
            , [PRIMARYKEY_Gateway
                , IS_A_FOREIGNKEY_IN . TABLE_GATEWAYS . OnKey(PRIMARYKEY_Gateway)]
        ]
    );


    create_Table(TABLE_SENSORS, PRIMARYKEY_Sensor

        , [//Fields
            [PRIMARYKEY_ApplicationDevice
                , TYPE_FOREIGNKEY
                , ITpings_PrimaryKey_In_Table . TABLE_APPLICATIONDEVICES]
            , [ITPINGS_SENSORNAME
                , TYPE_PAYLOAD_KEY
                , "TTN Payload key"]
        ]
        , [
            [PRIMARYKEY_ApplicationDevice
                , IS_A_FOREIGNKEY_IN . TABLE_APPLICATIONDEVICES . OnKey(PRIMARYKEY_ApplicationDevice)]
        ]
    );

    create_Table(TABLE_SENSORVALUES, PRIMARYKEY_SensorValue

        , [//Fields
            [PRIMARYKEY_Ping
                , TYPE_FOREIGNKEY
                , ITpings_PrimaryKey_In_Table . TABLE_PINGS]
            , [PRIMARYKEY_Sensor
                , TYPE_FOREIGNKEY
                , ITpings_PrimaryKey_In_Table . TABLE_SENSORS]
            , [ITPINGS_SENSORVALUE
                , TYPE_PAYLOAD_VALUE
                , "TTN Payload value"]
        ]
        , [//Foreign Keys
            [PRIMARYKEY_Ping
                , IS_A_FOREIGNKEY_IN . TABLE_PINGS . OnKey(PRIMARYKEY_Ping)],
            [PRIMARYKEY_Sensor
                , IS_A_FOREIGNKEY_IN . TABLE_SENSORS . OnKey(PRIMARYKEY_Sensor)]
        ]
    );

    //convert array to quoted string
    define('TYPE_EVENTTYPE', sprintf("ENUM('%s')", implode("','", ENUM_EVENTTYPES)));

    create_Table(TABLE_EVENTS, PRIMARYKEY_Events
        , [//Fields
            [PRIMARYKEY_Ping
                , TYPE_FOREIGNKEY
                , ITpings_PrimaryKey_In_Table . TABLE_PINGS]
            , [ITPINGS_EVENTTYPE
                , TYPE_EVENTTYPE . " DEFAULT '" . ENUM_EVENTTYPE_Log . "'"
                , "Event ENUM_EVENTTYPE values"]
            , [ITPINGS_EVENTLABEL
                , TYPE_EVENTLABEL
                , "Event label"]
            , [ITPINGS_EVENTVALUE
                , TYPE_EVENTVALUE
                , "Event text, can include POST BODY"]
        ]
        , [
            [PRIMARYKEY_Ping
                , IS_A_FOREIGNKEY_IN . TABLE_PINGS . OnKey(PRIMARYKEY_Ping)]
        ]
    );
}//end function createTables

//endregion

//region====================================================================================   create or replace Views

function SQL_create_or_replace_VIEW($viewname)
{
    //declare variables for constants so they can be used inside PHP strings
    $pingid = PRIMARYKEY_Ping;
    $appid = PRIMARYKEY_Application;
    $devid = PRIMARYKEY_Device;
    $gtwid = PRIMARYKEY_Gateway;
    $created = ITPINGS_CREATED_TIMESTAMP;
    $eventid = PRIMARYKEY_Events;
    $eventtype = ITPINGS_EVENTTYPE;
    $eventlabel = ITPINGS_EVENTLABEL;
    $eventvalue = ITPINGS_EVENTVALUE;
    $sensorid = PRIMARYKEY_Sensor;
    $sensorname = ITPINGS_SENSORNAME;
    $sensorvalue = ITPINGS_SENSORVALUE;
    $timestamp = TTN_timestamp;
    $time = TTN_time;
    $channel = TTN_channel;
    $rssi = TTN_rssi;
    $snr = TTN_snr;
    $rf_chain = TTN_rf_chain;

    $view = "";

    switch ($viewname) {
        case VIEWNAME_EVENTS:
            $view .= " P.$pingid , P.$created";
            $view .= ",E.$eventid , E.$eventtype , E.$eventlabel , E.$eventvalue";
            $view .= " FROM " . TABLE_EVENTS . " E ";
            $view .= " JOIN " . TABLE_PINGS . " P ON P.$pingid = E.$pingid";
            break;
        case VIEWNAME_APPLICATIONDEVICES:
            $view .= " AD.* ";
            $view .= " FROM " . TABLE_APPLICATIONDEVICES . " AD ";
            $view .= " JOIN " . TABLE_APPLICATIONS . " A ON A.$appid = AD.$appid";
            $view .= " JOIN " . TABLE_DEVICES . " D ON D.$devid = AD.$devid";
            $view .= " ORDER BY " . PRIMARYKEY_ApplicationDevice;
            break;
        case VIEWNAME_SENSORVALUES:
            $view .= " P.$pingid,P.$created";
            $view .= ",S.$sensorid , S.$sensorname , SV.$sensorvalue ";
            $view .= " FROM " . TABLE_SENSORVALUES . " SV ";
            $view .= " JOIN " . TABLE_SENSORS . " S ON S.$sensorid = SV.$sensorid";
            $view .= " JOIN " . TABLE_PINGS . " P ON P.$pingid = SV.$pingid";
            $view .= " ORDER BY " . ITPINGS_CREATED_TIMESTAMP . " ASC, SV.$sensorid";
            break;
        case VIEWNAME_PINGEDGATEWAYS:
            $view .= " P.$pingid,P.$created";
            $view .= ",PG.$timestamp, PG.$time, PG.$channel, PG.$rssi, PG.$snr, PG.$rf_chain ";
            $view .= ",G.* ";
            $view .= " FROM " . TABLE_PINGEDGATEWAYS . " PG ";
            $view .= " JOIN " . TABLE_GATEWAYS . " G ON G.$gtwid = PG.$gtwid";
            $view .= " JOIN " . TABLE_PINGS . " P ON P.$pingid = PG.$pingid";
            $view .= " ORDER BY " . ITPINGS_CREATED_TIMESTAMP . " ASC";
            break;
    }

    add_QueryLog("<h2>Create View: <b>$viewname</b></h2>");

    SQL_Query("CREATE OR REPLACE VIEW $viewname AS SELECT " . $view);
}

function create_ITpings_Views_in_Database()
{
    foreach (VIEWNAMES as $viewname) {
        SQL_create_or_replace_VIEW($viewname);
    }
}

//endregion====================================================================================  region create or replace Views

switch (ADMIN_ACTION) {
    case 'createtables':
        create_ITpings_Tables_in_Database();
        create_ITpings_Views_in_Database();
        break;
    case 'truncatetables':
        foreach (ITPINGS_TABLES as $index => $table) { // TODO get order right Referential Int. prevents deletions
            add_QueryLog("<h2>Truncate Table: <b>$table</b></h2>");
            SQL_Query("TRUNCATE TABLE $table;");
        }
        break;
    case 'droptables':
        foreach (ITPINGS_TABLES as $index => $table) {
            add_QueryLog("<h2>Drop Table: <b>$table</b></h2>");
            SQL_Query("DROP TABLE IF EXISTS $table;");
        }
        break;
    default:
        echo "incorrect action" . $urlVars['action'];
}

//show Query Log
foreach ($sqlLog as $key => $value) {
    echo $value;
}

