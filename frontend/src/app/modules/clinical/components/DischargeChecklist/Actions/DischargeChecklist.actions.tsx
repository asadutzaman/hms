import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import DischargeChecklistFormController from '../Form/DischargeChecklistForm.controller'
import DischargeChecklistViewController from '../View/DischargeChecklistView.controller'

export const DischargeChecklistAction = {
  COMMON_ACTION: {
    CREATE: {type: 'item', title: 'New Discharge Checklist', action: 'create', link: {to: ''}, permission: 'hms:discharge-checklist:create', component: DischargeChecklistFormController, className: 'grid-view-action'},
    EDIT: {type: 'item', title: 'Edit Discharge Checklist', action: 'edit', link: {to: ''}, permission: 'hms:discharge-checklist:edit', component: DischargeChecklistFormController, className: 'grid-view-action', modalId: 'edit'},
    VIEW: {type: 'item', title: 'View Discharge Checklist', action: 'view', link: {to: ''}, permission: 'hms:discharge-checklist:view', component: DischargeChecklistViewController, className: 'grid-view-action', modalId: 'view'},
    DELETE: {type: 'item', title: 'Delete Discharge Checklist', action: 'delete', link: {to: ''}, permission: 'hms:discharge-checklist:delete', component: '', className: 'grid-view-action'},
  },
  LIST_ITEM_ACTION: [
    {type: 'item', title: 'View', action: 'active', link: {to: ''}, permission: 'hms:discharge-checklist:view', component: ViewAction, className: '', modalId: 'view', icon: 'information-4'},
    {type: 'item', title: 'Edit', action: 'inactive', link: {to: ''}, permission: 'hms:discharge-checklist:edit', component: EditAction, className: '', modalId: 'edit', icon: 'pencil'},
    {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:discharge-checklist:delete', component: DeleteAction, className: '', modalId: 'delete', icon: 'trash'},
  ],
  BULK_ACTION: {
    permission: 'hms:discharge-checklist:multiSelect',
    action_list: [
      {type: 'item', title: 'Mark as Active', action: 'active', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Mark as Inactive', action: 'inactive', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:discharge-checklist:delete', component: '', className: 'grid-view-action'},
    ],
  },
}
