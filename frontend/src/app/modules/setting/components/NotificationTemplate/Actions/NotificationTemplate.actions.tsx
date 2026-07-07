import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import NotificationTemplateFormController from '../Form/NotificationTemplateForm.controller'
import NotificationTemplateViewController from '../View/NotificationTemplateView.controller'

export const NotificationTemplateAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New Notification Template',
      action: 'create',
      link: {to: ''},
      permission: 'auth:notification-template:create',
      component: NotificationTemplateFormController,
      className: 'grid-view-action',
    },
    EDIT: {
      type: 'item',
      title: 'Edit Notification Template',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:notification-template:edit',
      component: NotificationTemplateFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    VIEW: {
      type: 'item',
      title: 'View Notification Template',
      action: 'view',
      link: {to: ''},
      permission: 'auth:notification-template:view',
      component: NotificationTemplateViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    DELETE: {
      type: 'item',
      title: 'Delete Notification Template',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:notification-template:delete',
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
      permission: 'auth:notification-template:view',
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
      permission: 'auth:notification-template:edit',
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
      permission: 'auth:notification-template:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:notification-template:multiSelect',
    action_list: [
      {
        type: 'item',
        title: 'Delete',
        action: 'delete',
        link: {to: ''},
        permission: 'auth:notification-template:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}
