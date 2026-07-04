import DeleteAction from 'src/app/components/Actions/DeleteAction';
import EditAction from 'src/app/components/Actions/EditAction';
import ViewAction from 'src/app/components/Actions/ViewAction';
import DrugFormController from '../Form/DrugForm.controller';
import DrugViewController from '../View/DrugView.controller';

export const DrugAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New Drug',
      action: 'create',
      link: { to: '' },
      permission: 'auth:drug:create',
      component: DrugFormController,
      className: 'grid-view-action',
    },
    EDIT: {
      type: 'item',
      title: 'Edit Drug',
      action: 'edit',
      link: { to: '' },
      permission: 'auth:drug:edit',
      component: DrugFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    VIEW: {
      type: 'item',
      title: 'View Drug',
      action: 'view',
      link: { to: '' },
      permission: 'auth:drug:view',
      component: DrugViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    DELETE: {
      type: 'item',
      title: 'Delete Drug',
      action: 'delete',
      link: { to: '' },
      permission: 'auth:drug:delete',
      component: '',
      className: 'grid-view-action',
    },
  },
  LIST_ITEM_ACTION: [
    {
      type: 'item',
      title: 'View',
      action: 'active',
      link: { to: '' },
      permission: 'auth:drug:view',
      component: ViewAction,
      className: '',
      modalId: 'view',
      icon: 'information-4',
    },
    {
      type: 'item',
      title: 'Edit',
      action: 'inactive',
      link: { to: '' },
      permission: 'auth:drug:edit',
      component: EditAction,
      className: '',
      modalId: 'edit',
      icon: 'pencil',
    },
    {
      type: 'item',
      title: 'Delete',
      action: 'delete',
      link: { to: '' },
      permission: 'auth:drug:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:drug:multiSelect',
    action_list: [
      {
        type: 'item',
        title: 'Mark as Active',
        action: 'active',
        link: { to: '' },
        component: '',
        className: 'grid-view-action',
      },
      {
        type: 'item',
        title: 'Mark as Inactive',
        action: 'inactive',
        link: { to: '' },
        component: '',
        className: 'grid-view-action',
      },
      {
        type: 'item',
        title: 'Delete',
        action: 'delete',
        link: { to: '' },
        permission: 'auth:drug:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
};
