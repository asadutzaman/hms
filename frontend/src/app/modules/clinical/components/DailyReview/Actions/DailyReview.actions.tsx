import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import DailyReviewFormController from '../Form/DailyReviewForm.controller'
import DailyReviewViewController from '../View/DailyReviewView.controller'

export const DailyReviewAction = {
  COMMON_ACTION: {
    CREATE: {type: 'item', title: 'New Daily Review', action: 'create', link: {to: ''}, permission: 'hms:daily-review:create', component: DailyReviewFormController, className: 'grid-view-action'},
    EDIT: {type: 'item', title: 'Edit Daily Review', action: 'edit', link: {to: ''}, permission: 'hms:daily-review:edit', component: DailyReviewFormController, className: 'grid-view-action', modalId: 'edit'},
    VIEW: {type: 'item', title: 'View Daily Review', action: 'view', link: {to: ''}, permission: 'hms:daily-review:view', component: DailyReviewViewController, className: 'grid-view-action', modalId: 'view'},
    DELETE: {type: 'item', title: 'Delete Daily Review', action: 'delete', link: {to: ''}, permission: 'hms:daily-review:delete', component: '', className: 'grid-view-action'},
  },
  LIST_ITEM_ACTION: [
    {type: 'item', title: 'View', action: 'active', link: {to: ''}, permission: 'hms:daily-review:view', component: ViewAction, className: '', modalId: 'view', icon: 'information-4'},
    {type: 'item', title: 'Edit', action: 'inactive', link: {to: ''}, permission: 'hms:daily-review:edit', component: EditAction, className: '', modalId: 'edit', icon: 'pencil'},
    {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:daily-review:delete', component: DeleteAction, className: '', modalId: 'delete', icon: 'trash'},
  ],
  BULK_ACTION: {
    permission: 'hms:daily-review:multiSelect',
    action_list: [
      {type: 'item', title: 'Mark as Active', action: 'active', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Mark as Inactive', action: 'inactive', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:daily-review:delete', component: '', className: 'grid-view-action'},
    ],
  },
}
