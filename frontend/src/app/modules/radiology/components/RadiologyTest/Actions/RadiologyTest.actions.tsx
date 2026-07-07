import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import RadiologyTestFormController from '../Form/RadiologyTestForm.controller'
import RadiologyTestViewController from '../View/RadiologyTestView.controller'

export const RadiologyTestAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New Radiology Test',
      action: 'create',
      link: {to: ''},
      permission: 'auth:radiology-test:create',
      component: RadiologyTestFormController,
      className: 'grid-view-action',
    },
    EDIT: {
      type: 'item',
      title: 'Edit Radiology Test',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:radiology-test:edit',
      component: RadiologyTestFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    VIEW: {
      type: 'item',
      title: 'View Radiology Test',
      action: 'view',
      link: {to: ''},
      permission: 'auth:radiology-test:view',
      component: RadiologyTestViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    DELETE: {
      type: 'item',
      title: 'Delete Radiology Test',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:radiology-test:delete',
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
      permission: 'auth:radiology-test:view',
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
      permission: 'auth:radiology-test:edit',
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
      permission: 'auth:radiology-test:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:radiology-test:multiSelect',
    action_list: [
      {
        type: 'item',
        title: 'Delete',
        action: 'delete',
        link: {to: ''},
        permission: 'auth:radiology-test:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}
