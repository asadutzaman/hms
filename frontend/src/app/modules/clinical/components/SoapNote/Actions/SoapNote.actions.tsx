import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import SoapNoteFormController from '../Form/SoapNoteForm.controller'
import SoapNoteViewController from '../View/SoapNoteView.controller'

export const SoapNoteAction = {
  COMMON_ACTION: {
    CREATE: {type: 'item', title: 'New SOAP Note', action: 'create', link: {to: ''}, permission: 'hms:soap-note:create', component: SoapNoteFormController, className: 'grid-view-action'},
    EDIT: {type: 'item', title: 'Edit SOAP Note', action: 'edit', link: {to: ''}, permission: 'hms:soap-note:edit', component: SoapNoteFormController, className: 'grid-view-action', modalId: 'edit'},
    VIEW: {type: 'item', title: 'View SOAP Note', action: 'view', link: {to: ''}, permission: 'hms:soap-note:view', component: SoapNoteViewController, className: 'grid-view-action', modalId: 'view'},
    DELETE: {type: 'item', title: 'Delete SOAP Note', action: 'delete', link: {to: ''}, permission: 'hms:soap-note:delete', component: '', className: 'grid-view-action'},
  },
  LIST_ITEM_ACTION: [
    {type: 'item', title: 'View', action: 'active', link: {to: ''}, permission: 'hms:soap-note:view', component: ViewAction, className: '', modalId: 'view', icon: 'information-4'},
    {type: 'item', title: 'Edit', action: 'inactive', link: {to: ''}, permission: 'hms:soap-note:edit', component: EditAction, className: '', modalId: 'edit', icon: 'pencil'},
    {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:soap-note:delete', component: DeleteAction, className: '', modalId: 'delete', icon: 'trash'},
  ],
  BULK_ACTION: {
    permission: 'hms:soap-note:multiSelect',
    action_list: [
      {type: 'item', title: 'Mark as Active', action: 'active', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Mark as Inactive', action: 'inactive', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:soap-note:delete', component: '', className: 'grid-view-action'},
    ],
  },
}
