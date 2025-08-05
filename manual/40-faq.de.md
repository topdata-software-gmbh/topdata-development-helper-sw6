---
title: FAQ
---
# Häufig gestellte Fragen

**F: Wie setze ich meine Produktdatenbank vollständig zurück?**
A: Verwenden Sie `bin/console topdata:delete:all-products`, um alle Produkte zu entfernen.

**F: Wie kann ich verwaiste Medieneinträge bereinigen?**
A: Führen Sie `bin/console topdata:delete:invalid-media` aus, um ungültige Medien-Datensätze zu entfernen.

**F: Kann ich Plugin-Konfigurationen exportieren?**
A: Ja, verwenden Sie `bin/console topdata:dump:plugin-config`, um Konfigurationen in JSON-Dateien zu exportieren.

**F: Wie verwende ich die Twig-Debug-Funktion?**
A: Verwenden Sie `{{ print_r(variable) }}` in einer beliebigen Vorlage, um Variableninhalte auszugeben.