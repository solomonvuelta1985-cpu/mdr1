# Annex 18 Records Edit Type Selection Fix

## Task Description
Fix the error in annex18_records.php where the select type dropdown is not showing/populating correctly when editing records.

## Analysis
- The issue was in the JavaScript initialization in `api/annex18_edit.php`
- The form was trying to initialize before DOM elements were fully loaded when the edit modal was opened dynamically
- The type options were not being populated correctly based on the current classification

## Changes Made
- [x] Modified `api/annex18_edit.php` JavaScript to use a retry mechanism for initialization
- [x] Added immediate initialization call in addition to DOMContentLoaded event
- [x] Improved error handling for missing DOM elements
- [x] Ensured type options are populated correctly based on the current classification

## Additional Request
- Verified and confirmed that the main Annex 18 form (public/annex18.php) already includes the exact Type options as shown in the image: Livestock (Pig, Cow, Goat, Horse) and Poultry (Chicken, Duck). These are defined in the JavaScript `animalTypes` object and populate dynamically on Classification change. The technical notes modal also lists these options for user guidance. No code changes were needed for the create form.

## Testing
- [ ] Test editing an existing Annex 18 record
- [ ] Verify that the Type dropdown shows appropriate options based on Classification
- [ ] Confirm that the current Type value is pre-selected correctly
- [ ] Test changing Classification and ensure Type options update properly
- [ ] Test saving the edited record to ensure the changes persist without errors
- [ ] Test the main Annex 18 form (public/annex18.php) to confirm Type options populate correctly on Classification change

## Follow-up Steps
- [ ] If testing reveals issues, investigate further and make additional fixes
- [ ] Consider adding similar fixes to other annex edit forms if they have similar issues
