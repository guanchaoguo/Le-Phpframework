<?php
return array (
    'area' =>
        array (
            'required' => 'Bitte wählen Sie den Bedienbereich',
        ),
    'time_zone' =>
        array (
            'required' => 'Bitte wählen Sie die Zeitzone',
        ),
    'agent_name' =>
        array (
            'required' => 'Loginname darf nicht leer sein',
            'unique' => 'Loginname ist bereits vorhanden',
            'regex' => 'Loginname mit einem Buchstaben, 6-20 Buchstaben beginnen muß, unterstreicht und Zahlen',
        ),
    'real_name' =>
        array (
            'required' => 'Benutzername darf nicht leer sein',
            'regex' => 'Der Benutzername muss 3-20 Buchstaben, Unterstrichen, Zahlen und chinesischen Zusammensetzung sein',
        ),
    'password' =>
        array (
            'required' => 'Das Passwort darf nicht leer sein',
            'min' => 'Das Passwort kann nicht weniger als 6',
            'confirmed' => 'Passwort ein und bestätigen inkonsistent Passwort',
        ),
    'tel' =>
        array (
            'required' => 'Telefonnummer darf nicht leer sein',
        ),
    'email' =>
        array (
            'required' => 'E-Mail kann nicht leer sein',
            'email' => 'E-Mail-Format Fehler',
            'unique' => 'E-Mail ist bereits vorhanden',
        ),
    'hall_id' =>
        array (
            'required' => 'Bitte wählen Sie direkt unter der Haupthalle',
        ),
    'agent_code' =>
        array (
            'required' => 'Agenten-Code kann nicht leer sein',
            'unique' => 'Agents Code existiert bereits',
            'error' => 'Agenten der Code muss mit einem Buchstaben beginnen, 3-6 Buchstaben, Unterstrichen und Zahlen',
        ),
    'success' => 'Der erfolgreiche Betrieb',
    'save_fails' => 'Speichern fehlgeschlagen',
    'save_success' => 'erfolgreich gespeichert',
    'add_fails' => 'In fehlgeschlagen',
    'grade_id_error' => 'grade_id Parameterwert Fehler',
    'fails' => 'Operation fehlgeschlagen',
    'user_not_exist' => 'Spieler nicht existieren',
    'user_has_exist' => 'Spieler bereits vorhanden',
    'agent_not_exist' => 'Agents nicht existieren',
    'hall_not_exist' => 'Haupthalle existiert nicht',
    'game_not_exist' => 'Das Spiel ist nicht vorhanden',
    'limit_group_exist' => 'Limit-Gruppe ist bereits vorhanden',
    'limit_group_not_exist' => 'Es gibt keine Gruppierung Limit',
    'param_error' => 'Falscher Parameterwert',
    'insufficient_balance' => 'Unzureichende Mittel',
    'file_not_eixt' => 'Datei nicht vorhanden',
    'min_max_error' => 'Nicht weniger als ein Maximalwert gleich das Minimum',
    'last_max_error' => 'Schließlich darf maximal leer sein',
    'last_max_next_min' => 'Ein Maximalwert bei einem Minimum muss auf die gleich',
    'ip_error' => 'IP-Adresse ist falsch',
    'domain_error' => 'Falscher Domain-Name',
    'whitelist_not_exist' => 'Weiße Liste ist nicht vorhanden',
    'balance_str_error' => 'Limit muss eine Zahl',
    'export_requisite_uid' => 'Bitte wählen Sie einen Spieler und dann Exportdaten',
    'no_data_export' => 'Daten leer ist, können nicht exportiert werden',
    'hall_requiset' => 'Bitte wählen Sie eine Haupthalle',
    'agent_requiset' => 'Bitte wählen Sie einen Proxy',
    'player_requiset' => 'Bitte wählen Sie einen Spieler',
    'scale_error' => 'Es muss größer als 0 im Verhältnis berücksichtigt werden',
    'user_name' => 'Loginname mit einem Buchstaben, 6-20 Buchstaben beginnen muß, unterstreicht und Zahlen',
    'hall_has_data' => 'Die Haupthalle wurde über die Daten hinzugefügt',
    'alias' => 'Benutzername darf nicht leer sein',
    'user_sign_out' => 'Die Spieler wurden abgemeldet',
);