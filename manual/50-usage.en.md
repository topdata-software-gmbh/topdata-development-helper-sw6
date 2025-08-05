---
title: Usage
---
# Usage Guide

## Database Commands

- **Delete all products**: 
  ```bash
  bin/console topdata:delete:all-products
  ```
  
- **Clean invalid media**: 
  ```bash
  bin/console topdata:delete:invalid-media
  ```

## Configuration Management

- **Export configurations**: 
  ```bash
  bin/console topdata:dump:plugin-config
  ```
  
- **Import configurations**: 
  ```bash
  bin/console topdata:restore:plugin-config
  ```

## Debugging Tools

Use `{{ print_r(variable) }}` in Twig templates to inspect variables during development.