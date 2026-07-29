import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import ClinicalJobFormController from '../Form/ClinicalJobForm.controller'
import ClinicalJobViewController from '../View/ClinicalJobView.controller'

export const ClinicalJobAction = {
  COMMON_ACTION: {
    CREATE: {type: 'item', title: 'New Clinical Task', action: 'create', link: {to: ''}, permission: 'hms:clinical-job:create', component: ClinicalJobFormController, className: 'grid-view-action'},
    EDIT: {type: 'item', title: 'Edit Clinical Task', action: 'edit', link: {to: ''}, permission: 'hms:clinical-job:edit', component: ClinicalJobFormController, className: 'grid-view-action', modalId: 'edit'},
    VIEW: {type: 'item', title: 'View Clinical Task', action: 'view', link: {to: ''}, permission: 'hms:clinical-job:view', component: ClinicalJobViewController, className: 'grid-view-action', modalId: 'view'},
    DELETE: {type: 'item', title: 'Delete Clinical Task', action: 'delete', link: {to: ''}, permission: 'hms:clinical-job:delete', component: '', className: 'grid-view-action'},
  },
  LIST_ITEM_ACTION: [
    {type: 'item', title: 'View', action: 'active', link: {to: ''}, permission: 'hms:clinical-job:view', component: ViewAction, className: '', modalId: 'view', icon: 'information-4'},
    {type: 'item', title: 'Edit', action: 'inactive', link: {to: ''}, permission: 'hms:clinical-job:edit', component: EditAction, className: '', modalId: 'edit', icon: 'pencil'},
    {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:clinical-job:delete', component: DeleteAction, className: '', modalId: 'delete', icon: 'trash'},
  ],
  BULK_ACTION: {
    permission: 'hms:clinical-job:multiSelect',
    action_list: [
      {type: 'item', title: 'Mark as Active', action: 'active', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Mark as Inactive', action: 'inactive', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:clinical-job:delete', component: '', className: 'grid-view-action'},
    ],
  },
}
