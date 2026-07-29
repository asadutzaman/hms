import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import OrderSetFormController from '../Form/OrderSetForm.controller'
import OrderSetViewController from '../View/OrderSetView.controller'

export const OrderSetAction = {
  COMMON_ACTION: {
    CREATE: {type: 'item', title: 'New Order Set', action: 'create', link: {to: ''}, permission: 'hms:order-set:create', component: OrderSetFormController, className: 'grid-view-action'},
    EDIT: {type: 'item', title: 'Edit Order Set', action: 'edit', link: {to: ''}, permission: 'hms:order-set:edit', component: OrderSetFormController, className: 'grid-view-action', modalId: 'edit'},
    VIEW: {type: 'item', title: 'View Order Set', action: 'view', link: {to: ''}, permission: 'hms:order-set:view', component: OrderSetViewController, className: 'grid-view-action', modalId: 'view'},
    DELETE: {type: 'item', title: 'Delete Order Set', action: 'delete', link: {to: ''}, permission: 'hms:order-set:delete', component: '', className: 'grid-view-action'},
  },
  LIST_ITEM_ACTION: [
    {type: 'item', title: 'View', action: 'active', link: {to: ''}, permission: 'hms:order-set:view', component: ViewAction, className: '', modalId: 'view', icon: 'information-4'},
    {type: 'item', title: 'Edit', action: 'inactive', link: {to: ''}, permission: 'hms:order-set:edit', component: EditAction, className: '', modalId: 'edit', icon: 'pencil'},
    {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:order-set:delete', component: DeleteAction, className: '', modalId: 'delete', icon: 'trash'},
  ],
  BULK_ACTION: {
    permission: 'hms:order-set:multiSelect',
    action_list: [
      {type: 'item', title: 'Mark as Active', action: 'active', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Mark as Inactive', action: 'inactive', link: {to: ''}, component: '', className: 'grid-view-action'},
      {type: 'item', title: 'Delete', action: 'delete', link: {to: ''}, permission: 'hms:order-set:delete', component: '', className: 'grid-view-action'},
    ],
  },
}
