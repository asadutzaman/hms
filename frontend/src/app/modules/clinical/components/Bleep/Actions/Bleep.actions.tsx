import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import BleepFormController from '../Form/BleepForm.controller'
import BleepViewController from '../View/BleepView.controller'

export const BleepAction = {
  COMMON_ACTION: {
    CREATE: {type: 'item', title: 'New Bleep', action: 'create', link: {to: ''}, permission: 'hms:bleep:create', component: BleepFormController, className: 'grid-view-action'},
    EDIT: {type: 'item', title: 'Edit Bleep', action: 'edit', link: {to: ''}, permission: 'hms:bleep:edit', component: BleepFormController, className: 'grid-view-action', modalId: 'edit'},
    VIEW: {type: 'item', title: 'View Bleep', action: 'view', link: {to: ''}, permission: 'hms:bleep:view', component: BleepViewController, className: 'grid-view-action', modalId: 'view'},
    DELETE: {type: 'item', title: 'Delete Bleep', action: 'delete', link: {to: ''}, permission: 'hms:bleep:delete', component: '', className: 'grid-view-action'},
  },
  LIST_ITEM_ACTION: [
    {type: 'item', title: 'View', action: 'active', link: {to: ''}, permission: 'hms:bleep:view', component: ViewAction, className: '', modalId: 'view', icon: 'information-4'},
    {type: 'item', title: 'Edit', action: 'inactive', link: {to: ''}, permission: 'hms:bleep:edit', component: EditAction, className: '', modalId: 'edit', icon: 'pencil'},
    {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:bleep:delete', component: DeleteAction, className: '', modalId: 'delete', icon: 'trash'},
  ],
  BULK_ACTION: {
    permission: 'hms:bleep:multiSelect',
    action_list: [
      {type: 'item', title: 'Mark as Active', action: 'active', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Mark as Inactive', action: 'inactive', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:bleep:delete', component: '', className: 'grid-view-action'},
    ],
  },
}
