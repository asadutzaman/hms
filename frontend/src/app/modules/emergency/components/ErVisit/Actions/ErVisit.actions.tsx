import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import ErVisitFormController from '../Form/ErVisitForm.controller'
import ErVisitViewController from '../View/ErVisitView.controller'

export const ErVisitAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'Register Emergency Patient',
      action: 'create',
      link: {to: ''},
      permission: 'auth:er-visit:create',
      component: ErVisitFormController,
      className: 'grid-view-action',
    },
    EDIT: {
      type: 'item',
      title: 'Edit ER Visit',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:er-visit:edit',
      component: ErVisitFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    VIEW: {
      type: 'item',
      title: 'View ER Visit',
      action: 'view',
      link: {to: ''},
      permission: 'auth:er-visit:view',
      component: ErVisitViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    DELETE: {
      type: 'item',
      title: 'Delete ER Visit',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:er-visit:delete',
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
      permission: 'auth:er-visit:view',
      component: ViewAction,
      className: '',
      modalId: 'view',
      icon: 'information-4',
    },
    {
      type: 'item',
      title: 'Delete',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:er-visit:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:er-visit:multiSelect',
    action_list: [
      {
        type: 'item',
        title: 'Delete',
        action: 'delete',
        link: {to: ''},
        permission: 'auth:er-visit:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}
