# TODO Steps for Laravel Repository Binding Fix - COMPLETE

## Steps:

1. [x] Edit bootstrap/providers.php to register RepositoryServiceProvider
2. [x] Clear Laravel caches (config:clear, route:clear)
3. [x] Herd setup: Use `herd link` instead of `valet link`. Site at http://hauniakos.test (port 80 occupied by Herd).
4. [x] `herd --version` confirms Herd 1.26.0 installed.
5. [x] php artisan serve workaround: Herd handles serving.

## COMPLETE: City::boardingHouse() fixed in CityRepository.php (withCount plural). Test http://hauniakos.test ✓
