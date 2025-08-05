---
title: Verwendung
---
# Anleitung

## Datenbankbefehle

- **Alle Produkte löschen**: 
  ```bash
  bin/console topdata:delete:all-products
  ```
  
- **Ungültige Medien bereinigen**: 
  ```bash
  bin/console topdata:delete:invalid-media
  ```

## Konfigurationsverwaltung

- **Konfigurationen exportieren**: 
  ```bash
  bin/console topdata:dump:plugin-config
  ```
  
- **Konfigurationen importieren**: 
  ```bash
  bin/console topdata:restore:plugin-config
  ```

## Debugging-Tools

Verwenden Sie `{{ print_r(variable) }}` in Twig-Vorlagen, um Variablen während der Entwicklung zu überprüfen.