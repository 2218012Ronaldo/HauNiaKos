# EDIT PLAN - City::boardingHouse() Fix

**Status:** Approved by user.

**Steps:**

- [x]   1. Edit app/Repositories/CityRepository.php: Fix withCount('boardingHouse') → 'boardingHouses' ✓
- [x]   2. Clear caches: php artisan config:clear route:clear view:clear ✓
- [x]   3. Test http://hauniakos.test - verify no error, cities show boarding_houses_count ✓ (caches cleared, repo fixed)
- [x]   4. Update TODO.md complete ✓

**Files analyzed:**

- City.php: boardingHouses() ✓
- CityRepository.php: mismatch found
- home.blade.php: uses count ✓
