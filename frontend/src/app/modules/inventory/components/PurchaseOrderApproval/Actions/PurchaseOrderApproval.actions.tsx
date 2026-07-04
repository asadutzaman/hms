import ViewAction from 'src/app/components/Actions/ViewAction'
import PurchaseOrderApprovalViewController from '../View/PurchaseOrderApprovalView.controller'

export const PurchaseOrderApprovalAction = {
  COMMON_ACTION: {
    VIEW: {
      type: 'item',
      title: 'View Purchase Order',
      action: 'view',
      link: {to: ''},
      permission: 'auth:purchaseOrderApproval:view',
      component: PurchaseOrderApprovalViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
  },
  LIST_ITEM_ACTION: [
    {
      type: 'item',
      title: 'View',
      action: 'active',
      link: {to: ''},
      permission: 'auth:purchaseOrderApproval:view',
      component: ViewAction,
      className: '',
      modalId: 'view',
      icon: 'information-4',
    },
  ],
}
