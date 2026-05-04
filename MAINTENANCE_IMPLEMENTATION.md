# Maintenance Complaint System - Quick Reference

## What Changed

### Database (Migration: 2026_05_01_000001)
- Added `needs_inspection` - boolean flag for inspection requirement
- Added `payment_responsibility` - enum (owner/tenant) for who pays
- Added `admin_notes` - admin's assessment of the complaint
- Added `inspection_notes` - findings from inspection
- Added `approved_for_repair_at` - when repair was approved
- Added `under_repair_at` - when repair started
- Added `service_provider_assigned_at` - when service was arranged

### Models
**MaintenanceRequest.php**
- Added new fields to `$fillable` array
- Added datetime/boolean casts

### Controllers
**MaintenanceRequestController.php**
- `adminApproveForRepair()` - Direct repair approval (no inspection)
- `adminRequestInspection()` - Request inspection for unclear cases
- `adminCompleteInspection()` - Record inspection results and decide
- `adminUpdateRepairStatus()` - Track repair progress (started/completed)

**AdminController.php**
- Updated `maintenanceRequests()` status counts to use new statuses

### Routes (web.php)
```php
Route::post('/maintenance/{maintenanceRequest}/approve', ...)->name('maintenance.admin.approve');
Route::post('/maintenance/{maintenanceRequest}/request-inspection', ...)->name('maintenance.admin.request-inspection');
Route::post('/maintenance/{maintenanceRequest}/inspection-complete', ...)->name('maintenance.admin.inspection-complete');
Route::post('/maintenance/{maintenanceRequest}/repair-status', ...)->name('maintenance.admin.update-repair-status');
```

### Views
**admin/maintenance.blade.php**
- Updated page title to "Maintenance Complaints"
- Updated stat cards with new statuses
- Updated status filter dropdown
- Updated status tabs
- Complete redesign of maintenance table with:
  - Issue category column
  - Payment responsibility column
  - Action buttons for each complaint
- Added comprehensive modal system for:
  - Complaint details
  - Approve for repair form
  - Request inspection form
  - Start repair form
  - Mark resolved form

**tenant/maintenance.blade.php**
- Updated stat cards with new status counts
- Updated table headers to show responsibility
- Updated table rows to show:
  - Payment responsibility badge
  - Inspection required badge
  - New status values
- Updated modal to show:
  - Payment responsibility
  - Admin notes
  - Inspection findings
  - Updated timeline with all status transitions

## Workflow Summary

### For Admin
1. **Pending Complaint Arrives**
   - View complaint details
   - Two options:
     - Approve for immediate repair (obvious issue)
     - Request inspection (unclear issue)

2. **If Direct Approval**
   - Choose who pays (Owner/Tenant)
   - Add notes
   - System arranges service

3. **If Inspection Needed**
   - Wait for inspection
   - Review findings
   - Decide: Approve or Reject
   - Set payment responsibility if approved

4. **Track Repair**
   - Mark when repair starts (Under Repair)
   - Mark when repair completes (Resolved)
   - Tenant gets notifications at each step

### For Tenant
1. **Report Issue**
   - Select property
   - Choose category & priority
   - Describe problem
   - Submit

2. **Track Status**
   - See if inspection is needed
   - See payment responsibility
   - Get notified on updates
   - View admin notes and inspection findings

## Key Status Statuses

| Status | Meaning | What's Next |
|--------|---------|-------------|
| Pending | Awaiting admin review | Admin decision |
| Approved for Repair | Admin approved, service arranged | Work starts |
| Under Repair | Repair work in progress | Completion |
| Resolved | Work complete | Closed |
| Rejected | Not approved | Closed |

## Payment Responsibility

- **Owner Pays:** Normal wear & tear, property faults, old damage
- **Tenant Pays:** Tenant-caused damage

## Notifications Sent

| Event | Notified |
|-------|----------|
| Complaint submitted | Owner |
| Approved for repair | Tenant + Owner |
| Inspection requested | Tenant |
| Repair started | Tenant |
| Repair completed | Tenant |
| Complaint rejected | Tenant |

## Database Summary

| Field | Type | Purpose |
|-------|------|---------|
| needs_inspection | boolean | Flag if inspection required |
| payment_responsibility | enum | Owner or Tenant |
| admin_notes | text | Admin's assessment |
| inspection_notes | text | Inspection findings |
| approved_for_repair_at | timestamp | When approved |
| under_repair_at | timestamp | When work started |

## Testing Workflow

1. Create tenant user and log in
2. Report a maintenance complaint for active rental
3. Log in as admin
4. View pending complaint
5. Test two paths:
   - **Path A:** Approve for repair → Set responsibility → Track status
   - **Path B:** Request inspection → Complete inspection → Approve/Reject

## File Changes Summary

- [app/Models/MaintenanceRequest.php](app/Models/MaintenanceRequest.php) - Updated fillable & casts
- [app/Http/Controllers/MaintenanceRequestController.php](app/Http/Controllers/MaintenanceRequestController.php) - Added 4 new admin methods
- [app/Http/Controllers/AdminController.php](app/Http/Controllers/AdminController.php) - Updated status counts
- [routes/web.php](routes/web.php) - Added 4 new routes
- [resources/views/admin/maintenance.blade.php](resources/views/admin/maintenance.blade.php) - Complete redesign
- [resources/views/tenant/maintenance.blade.php](resources/views/tenant/maintenance.blade.php) - Updated with new data
- [database/migrations/2026_05_01_000001_update_maintenance_requests_for_complaint_workflow.php](database/migrations/2026_05_01_000001_update_maintenance_requests_for_complaint_workflow.php) - New migration
