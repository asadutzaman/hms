import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import CodeBlueEventFormController from '../Form/CodeBlueEventForm.controller'
import CodeBlueEventViewController from '../View/CodeBlueEventView.controller'

export const CodeBlueEventAction = {
  COMMON_ACTION: {
    CREATE: {type: 'item', title: 'New Code Blue Event', action: 'create', link: {to: ''}, permission: 'hms:code-blue-event:create', component: CodeBlueEventFormController, className: 'grid-view-action'},
    EDIT: {type: 'item', title: 'Edit Code Blue Event', action: 'edit', link: {to: ''}, permission: 'hms:code-blue-event:edit', component: CodeBlueEventFormController, className: 'grid-view-action', modalId: 'edit'},
    VIEW: {type: 'item', title: 'View Code Blue Event', action: 'view', link: {to: ''}, permission: 'hms:code-blue-event:view', component: CodeBlueEventViewController, className: 'grid-view-action', modalId: 'view'},
    DELETE: {type: 'item', title: 'Delete Code Blue Event', action: 'delete', link: {to: ''}, permission: 'hms:code-blue-event:delete', component: '', className: 'grid-view-action'},
  },
  LIST_ITEM_ACTION: [
    {type: 'item', title: 'View', action: 'active', link: {to: ''}, permission: 'hms:code-blue-event:view', component: ViewAction, className: '', modalId: 'view', icon: 'information-4'},
    {type: 'item', title: 'Edit', action: 'inactive', link: {to: ''}, permission: 'hms:code-blue-event:edit', component: EditAction, className: '', modalId: 'edit', icon: 'pencil'},
    {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:code-blue-event:delete', component: DeleteAction, className: '', modalId: 'delete', icon: 'trash'},
  ],
  BULK_ACTION: {
    permission: 'hms:code-blue-event:multiSelect',
    action_list: [
      {type: 'item', title: 'Mark as Active', action: 'active', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Mark as Inactive', action: 'inactive', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:code-blue-event:delete', component: '', className: 'grid-view-action'},
    ],
  },
}
