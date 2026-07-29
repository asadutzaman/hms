import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import AtoeAssessmentFormController from '../Form/AtoeAssessmentForm.controller'
import AtoeAssessmentViewController from '../View/AtoeAssessmentView.controller'

export const AtoeAssessmentAction = {
  COMMON_ACTION: {
    CREATE: {type: 'item', title: 'New A-to-E Assessment', action: 'create', link: {to: ''}, permission: 'hms:atoe-assessment:create', component: AtoeAssessmentFormController, className: 'grid-view-action'},
    EDIT: {type: 'item', title: 'Edit A-to-E Assessment', action: 'edit', link: {to: ''}, permission: 'hms:atoe-assessment:edit', component: AtoeAssessmentFormController, className: 'grid-view-action', modalId: 'edit'},
    VIEW: {type: 'item', title: 'View A-to-E Assessment', action: 'view', link: {to: ''}, permission: 'hms:atoe-assessment:view', component: AtoeAssessmentViewController, className: 'grid-view-action', modalId: 'view'},
    DELETE: {type: 'item', title: 'Delete A-to-E Assessment', action: 'delete', link: {to: ''}, permission: 'hms:atoe-assessment:delete', component: '', className: 'grid-view-action'},
  },
  LIST_ITEM_ACTION: [
    {type: 'item', title: 'View', action: 'active', link: {to: ''}, permission: 'hms:atoe-assessment:view', component: ViewAction, className: '', modalId: 'view', icon: 'information-4'},
    {type: 'item', title: 'Edit', action: 'inactive', link: {to: ''}, permission: 'hms:atoe-assessment:edit', component: EditAction, className: '', modalId: 'edit', icon: 'pencil'},
    {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:atoe-assessment:delete', component: DeleteAction, className: '', modalId: 'delete', icon: 'trash'},
  ],
  BULK_ACTION: {
    permission: 'hms:atoe-assessment:multiSelect',
    action_list: [
      {type: 'item', title: 'Mark as Active', action: 'active', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Mark as Inactive', action: 'inactive', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:atoe-assessment:delete', component: '', className: 'grid-view-action'},
    ],
  },
}
