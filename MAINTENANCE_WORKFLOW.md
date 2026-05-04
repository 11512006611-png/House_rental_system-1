# Maintenance Complaint Workflow - Implementation Guide

## System Overview

The maintenance complaint system allows tenants to report issues, and the admin manages all repair arrangements. The key principle is that **admin inspects only when necessary** (unclear issues or responsibility verification), while obvious issues go directly to repair.

## Workflow Statuses

1. **Pending** - Initial state when tenant reports a complaint
2. **Approved for Repair** - Admin has approved the complaint and arranged repair service
3. **Under Repair** - Repair work is in progress
4. **Resolved** - Repair work is completed
5. **Rejected** - Complaint was rejected after review or inspection

## Admin Workflow

### 1. Reviewing Pending Complaints
- Admin accesses `/admin/maintenance` dashboard
- See all pending complaints with tenant info, issue category, and priority
- Each complaint has two primary actions based on severity and clarity

### 2. Direct Approval (No Inspection Needed)
**For obvious/visible issues:** broken socket, major water leak, security concern, etc.

**Steps:**
1. Click the view icon on a pending complaint
2. Select "Approve for Repair (No Inspection)" button
3. Choose payment responsibility:
   - **Owner Pays:** For normal wear, property faults, old damage
   - **Tenant Pays:** For tenant-caused damage
4. Add admin notes explaining why direct repair is appropriate
5. System:
   - Changes status to "Approved for Repair"
   - Notifies both tenant and owner
   - Sets `approved_for_repair_at` timestamp
   - Records `payment_responsibility`

### 3. Request Inspection (For Unclear Cases)
**When needed:**
- Cause of damage is unclear
- Payment responsibility must be confirmed
- Property condition assessment required

**Steps:**
1. Click view icon on pending complaint
2. Select "Request Inspection First" button
3. Choose inspection reason:
   - Unclear cause
   - Verify responsibility
   - Condition assessment
4. Add notes for inspection team
5. System:
   - Keeps status as "Pending" 
   - Sets `needs_inspection = true`
   - Notifies tenant that inspection is required
   - Records `admin_notes` with inspection details

### 4. Complete Inspection & Decide
After inspection is conducted:

**Steps:**
1. Record inspection findings in inspection_notes field
2. Decision:
   - **Approve for Repair:** Choose payer (owner/tenant), system changes to "Approved for Repair"
   - **Reject:** Provide rejection reason, system changes to "Rejected"
3. Tenant is notified of decision

### 5. Track Repair Progress
Once approved:

**Start Repair:**
- Click "Start Repair" button when work begins
- Status changes to "Under Repair"
- Sets `under_repair_at` timestamp

**Mark Resolved:**
- Click "Mark Resolved" when work is complete
- Status changes to "Resolved"
- Sets `resolved_at` timestamp

## Tenant Workflow

### 1. Reporting a Complaint
**Location:** `/tenant/maintenance`

**Steps:**
1. Select active rental property
2. Choose issue category:
   - Water
   - Electricity
   - Plumbing
   - Security
   - Cleaning
   - Other
3. Set priority (Low, Medium, High, Urgent)
4. Enter detailed issue description
5. Optionally set preferred visit date
6. Submit complaint

**System Response:**
- Complaint created with status "Pending Review"
- Admin receives notification

### 2. Tracking Complaint Status
**Complaint Progress Display:**
- Real-time status display on dashboard
- Status updates:
  - Pending Review (awaiting admin decision)
  - Inspection Required (if admin needs verification)
  - Approved for Repair (repair is being arranged)
  - Under Repair (work in progress)
  - Resolved (completed)
  - Rejected (not approved)

**Complaint Details Modal:**
- Issue description
- Admin notes (visible to tenant)
- Inspection findings (if inspection performed)
- Payment responsibility (who pays)
- Timeline showing all status transitions with timestamps
- Emergency contact info if urgent

### 3. Payment Responsibility
Tenant can see in their complaint details:
- **Owner Pays:** Normal wear and tear, property faults, old/preexisting damage
- **Tenant Pays:** Damage caused by tenant actions
- Status displayed as badge (red for tenant, green for owner)

## Database Fields Added

### maintenance_requests Table

New columns:
- `needs_inspection` (boolean) - Flag for inspection requirement
- `payment_responsibility` (enum: owner/tenant) - Who pays for repair
- `admin_notes` (text) - Admin's assessment and notes
- `inspection_notes` (text) - Findings from inspection
- `service_provider_assigned_at` (timestamp) - When service was arranged
- `approved_for_repair_at` (timestamp) - When repair was approved
- `under_repair_at` (timestamp) - When repair started

## Status Transitions

```
Pending
├─→ [Direct Approve] → Approved for Repair → Under Repair → Resolved
├─→ [Inspect] → Pending (needs_inspection = true)
│   ├─→ [Approve] → Approved for Repair → Under Repair → Resolved
│   └─→ [Reject] → Rejected
└─→ [Later Reject] → Rejected
```

## API Routes (Admin Actions)

```
POST /admin/maintenance/{id}/approve
    - Approve for direct repair
    - Params: payment_responsibility, admin_notes

POST /admin/maintenance/{id}/request-inspection
    - Request inspection
    - Params: inspection_reason, admin_notes

POST /admin/maintenance/{id}/inspection-complete
    - Complete inspection and decide
    - Params: inspection_notes, approve_repair, payment_responsibility, rejection_reason

POST /admin/maintenance/{id}/repair-status
    - Update repair status
    - Params: status (under_repair|resolved)
```

## Key Features

1. **Flexible Decision Making:**
   - Obvious issues get direct approval
   - Unclear cases trigger inspection workflow
   - Admin has full visibility and control

2. **Clear Payment Responsibility:**
   - Admin explicitly sets who pays
   - Visible to both tenant and owner
   - Helps manage disputes

3. **Audit Trail:**
   - Complete timestamp history
   - Admin notes recorded
   - Inspection findings documented
   - Visible to all parties

4. **Tenant Transparency:**
   - Tenants see all updates
   - Know payment responsibility
   - Can track repair progress
   - Receive notifications

5. **Owner/Admin Efficiency:**
   - No unnecessary inspections
   - Clear decision criteria
   - Direct arrangement of repairs
   - Centralized management

## Notification System

Admin actions trigger notifications:
- **Complaint Approved:** Tenant and owner notified of approval and payment responsibility
- **Inspection Requested:** Tenant notified that inspection is required
- **Status Updated:** Tenant notified when status changes
- **Complaint Rejected:** Tenant notified with rejection reason

## Admin Dashboard Stats

Displays counts for:
- **Pending Review:** Complaints waiting for admin decision
- **Approved for Repair:** Complaints approved, arrangements being made
- **Under Repair:** Active repair work
- **Resolved:** Completed repairs

## Filter Options

Admin can filter complaints by:
- Status
- Priority
- Category (Water, Electricity, Plumbing, etc.)
- Search by tenant name, email, or property

## Emergency Contact

Tenant dashboard displays admin emergency contact for urgent issues requiring immediate attention.

## Implementation Complete ✓

All components have been implemented:
- [x] Database schema with new fields
- [x] Model updated with fillable fields and casts
- [x] Admin action methods in controller
- [x] Routes configured
- [x] Admin dashboard with complaint management UI
- [x] Tenant view with complaint tracking
- [x] Notification system integration
- [x] Migration executed successfully
