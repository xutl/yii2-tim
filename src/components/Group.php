<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace xutl\tim\components;

use xutl\tim\Client;

/**
 * Class Group
 * @package xutl\tim\components
 */
class Group extends Client
{
    /**
     * @param int $limit 限制回包中GroupIdList中群组的个数，不得超过10000；
     * @param int $next 控制分页。对于分页请求，第一次填0，后面的请求填上一次返回的Next字段，当返回的Next为0，代表所有的群都拉取到了；
     * 假设需要分页拉取，每页展示20个，则第一页的请求参数应当为{“Limit” : 20, “Next” : 0}，第二页的请求参数应当为{“Limit” : 20, “Next” : 上次返回的Next字段}，依此类推；
     * @param string $type 可以指定拉取的群组所属的群组形态，如Public，Private，ChatRoom、AVChatRoom和BChatRoom。
     * @return array
     */
    public function lis($limit = 1000, $next = 0, $type = 'Public')
    {
        $response = $this->post('group_open_http_svc/get_appid_group_list', [
            'Limit' => $limit,
            'Next' => $next,
            'GroupType' => $type
        ])->send();
        return $response->data;
    }

    /**
     * 创建群组
     * @param array $params
     * @return array
     */
    public function create(array $params)
    {
        $response = $this->post('group_open_http_svc/create_group', $params);
        return $response->data;
    }

    /**
     * 创建群组基础接口
     * @param string $type 群组形态，包括Public（公开群），Private（私密群），ChatRoom（聊天室），AVChatRoom（互动直播聊天室），BChatRoom（在线成员广播大群）。
     * @param string $name 群名称，最长30字节。
     * @param string $ownerId 群主id，自动添加到群成员中。如果不填，群没有群主。
     * @param string $id 为了使得群组ID更加简单，便于记忆传播，腾讯云支持APP在通过REST API创建群组时自定义群组ID。详情参见：自定义群组ID。
     * @return array
     */
    public function createBasic($type, $name, $ownerId = null, $id = null)
    {
        $params = [
            'Type' => $type,
            'Name' => $name,
        ];
        if (!is_null($ownerId)) $params['Owner_Account'] = $ownerId;
        if (!is_null($id)) $params['GroupId'] = $id;
        return $this->create($params);
    }

    /**
     * 获取群组详细资料
     * @param array $groupIdList
     * @return object
     */
    public function info($groupIdList = [])
    {
        return $this->post('group_open_http_svc/create_group', 'POST', [
            'GroupIdList' => $groupIdList
        ])->send();
    }

    /**
     * 获取群组成员详细资料
     * @param string $id
     * @param int $limit
     * @param int $offset
     * @param array $roleFilter
     * @param array $infoFilter
     * @return object
     */
    public function groupMemberInfo($id, $limit = 100, $offset = 0, $roleFilter = [], $infoFilter = [])
    {
        $params = ['GroupId' => $id, 'Limit' => $limit, 'Offset' => $offset];
        if ($roleFilter) $params['MemberRoleFilter'] = $roleFilter;
        if ($infoFilter) $params['MemberInfoFilter'] = $infoFilter;
        return $this->post('group_open_http_svc/get_group_member_info', [
            'GroupId' => $id
        ])->send();
    }

    /**
     * 在群组中发送系统通知
     * @param string $id
     * @param string $content
     * @param array $members
     * @return object
     */
    public function groupSendSystemNotification($id, $content, $members = [])
    {
        $params = ['GroupId' => $id, 'Content' => $content];
        if ($members) $params['ToMembers_Account'] = $members;
        return $this->post('group_open_http_svc/send_group_system_notification', $params)->send();
    }
}