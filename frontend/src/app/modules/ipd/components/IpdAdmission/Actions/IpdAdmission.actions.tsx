import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import IpdAdmissionFormController from '../Form/IpdAdmissionForm.controller'
import IpdAdmissionViewController from '../View/IpdAdmissionView.controller'

export const IpdAdmissionAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New Admission',
      action: 'create',
      link: {to: ''},
      permission: 'auth:ipd-admission:create',
      component: IpdAdmissionFormController,
      className: 'grid-view-action',
    },
    EDIT: {
      type: 'item',
      title: 'Edit Admission',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:ipd-admission:edit',
      component: IpdAdmissionFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    VIEW: {
      type: 'item',
      title: 'View Admission',
      action: 'view',
      link: {to: ''},
      permission: 'auth:ipd-admission:view',
      component: IpdAdmissionViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    DELETE: {
      type: 'item',
      title: 'Delete Admission',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:ipd-admission:delete',
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
      permission: 'auth:ipd-admission:view',
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
      permission: 'auth:ipd-admission:edit',
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
      permission: 'auth:ipd-admission:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:ipd-admission:multiSelect',
    action_list: [
      {
        type: 'item',
        title: 'Delete',
        action: 'delete',
        link: {to: ''},
        permission: 'auth:ipd-admission:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}
