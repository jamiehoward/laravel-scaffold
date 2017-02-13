# Laravel Scaffolding Engine

## Summary

This project will allow you to specify a `schema.json` file that can be used to generate architecture scaffolding for your models. There are other projects that have similar functionality, but this was a great coding exercise.

## Generated architecture

1. Model
2. Controller
3. CRUD Routes
4. Migration
5. Factory
6. Seeder
7. Test

## Future Needs

- The schema.json file needs to be specified as an command argument.
- Require `tightenco/lambo` and then use its command from vendor directory.
- Turn into a Laravel package.
- Use stubs instead of inline PHP generation. 