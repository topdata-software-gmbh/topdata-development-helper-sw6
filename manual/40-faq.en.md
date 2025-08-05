---
title: FAQ
---
# Frequently Asked Questions

**Q: How do I completely reset my product database?**
A: Use `bin/console topdata:delete:all-products` to remove all products.

**Q: How can I clean up orphaned media entries?**
A: Execute `bin/console topdata:delete:invalid-media` to remove invalid media records.

**Q: Can I export plugin configurations?**
A: Yes, use `bin/console topdata:dump:plugin-config` to export configurations to JSON files.

**Q: How do I use the Twig debug function?**
A: Use `{{ print_r(variable) }}` in any template to output variable contents.