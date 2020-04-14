export const MessageMode = {
  unread: {
    status: 'unread',
    text: '未读信',
    value: 1
  },
  read: {
    status: 'read',
    text: '已读信',
    value: 2
  },
  sent: {
    status: 'sent',
    text: '已发送',
    value: 3
  },
  temp: {
    status: 'temp',
    text: '草稿箱',
    value: 4
  }
}
//任务状态 0-待接收 1-进行中按时 2-已完成按时 3-进行中超时 4-已完成超时
export const MessageStatus = {
  0: {
    text: '待处理',
    classes: 'waiting'
  },
  1: {
    text: '已接收',
    classes: 'pending'
  },
  2: {
    text: '已完成',
    classes: 'done'
  },
  3: {
    text: '超时',
    classes: 'timeout'
  },
  4: {
    text: '超时完成',
    classes: 'timeout'
  }
}

export const MessageFinishStatus = {
  1: {
    text: '待处理'
  },
  2: {
    text: '已接收',
  },
  3: {
    text: '已完成'
  }
}
