import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import PurchaseOrderFormController from '../Form/PurchaseOrderForm.controller'
import PurchaseOrderViewController from '../View/PurchaseOrderView.controller'

export const PurchaseOrderAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New Purchase Order',
      action: 'create',
      link: {to: ''},
      permission: 'auth:purchaseOrder:create',
      component: PurchaseOrderFormController,
      className: 'grid-view-action',
    },
    EDIT: {
      type: 'item',
      title: 'Edit Purchase Order',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:purchaseOrder:edit',
      component: PurchaseOrderFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    VIEW: {
      type: 'item',
      title: 'View Purchase Order',
      action: 'view',
      link: {to: ''},
      permission: 'auth:purchaseOrder:view',
      component: PurchaseOrderViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    DELETE: {
      type: 'item',
      title: 'Delete Purchase Order',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:purchaseOrder:delete',
      component: '',
      className: 'grid-view-action',
    },
  },
  LIST_ITEM_ACTION: [
    {
      type: 'item',
      title: 'View',
      action: 'active',
      link: {to: ''},
      permission: 'auth:purchaseOrder:view',
      component: ViewAction,
      className: '',
      modalId: 'view',
      icon: 'information-4',
    },
    {
      type: 'item',
      title: 'Edit',
      action: 'inactive',
      link: {to: ''},
      permission: 'auth:purchaseOrder:edit',
      component: EditAction,
      className: '',
      modalId: 'edit',
      icon: 'pencil',
    },
    {
      type: 'item',
      title: 'Delete',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:purchaseOrder:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:purchaseOrder:multiSelect',
    action_list: [
      {
        type: 'item',
        title: 'Delete',
        action: 'delete',
        link: {to: ''},
        permission: 'auth:purchaseOrder:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}
