import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import ShiftHandoverFormController from '../Form/ShiftHandoverForm.controller'
import ShiftHandoverViewController from '../View/ShiftHandoverView.controller'

export const ShiftHandoverAction = {
  COMMON_ACTION: {
    CREATE: {type: 'item', title: 'New Shift Handover', action: 'create', link: {to: ''}, permission: 'hms:shift-handover:create', component: ShiftHandoverFormController, className: 'grid-view-action'},
    EDIT: {type: 'item', title: 'Edit Shift Handover', action: 'edit', link: {to: ''}, permission: 'hms:shift-handover:edit', component: ShiftHandoverFormController, className: 'grid-view-action', modalId: 'edit'},
    VIEW: {type: 'item', title: 'View Shift Handover', action: 'view', link: {to: ''}, permission: 'hms:shift-handover:view', component: ShiftHandoverViewController, className: 'grid-view-action', modalId: 'view'},
    DELETE: {type: 'item', title: 'Delete Shift Handover', action: 'delete', link: {to: ''}, permission: 'hms:shift-handover:delete', component: '', className: 'grid-view-action'},
  },
  LIST_ITEM_ACTION: [
    {type: 'item', title: 'View', action: 'active', link: {to: ''}, permission: 'hms:shift-handover:view', component: ViewAction, className: '', modalId: 'view', icon: 'information-4'},
    {type: 'item', title: 'Edit', action: 'inactive', link: {to: ''}, permission: 'hms:shift-handover:edit', component: EditAction, className: '', modalId: 'edit', icon: 'pencil'},
    {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:shift-handover:delete', component: DeleteAction, className: '', modalId: 'delete', icon: 'trash'},
  ],
  BULK_ACTION: {
    permission: 'hms:shift-handover:multiSelect',
    action_list: [
      {type: 'item', title: 'Mark as Active', action: 'active', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Mark as Inactive', action: 'inactive', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:shift-handover:delete', component: '', className: 'grid-view-action'},
    ],
  },
}
