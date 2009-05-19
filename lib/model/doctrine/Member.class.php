<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

class Member extends BaseMember
{
  public function getProfiles($viewableCheck = false, $myMemberId = null)
  {
    if ($viewableCheck)
    {
      return Doctrine::getTable('MemberProfile')->getViewableProfileListByMemberId($this->getId(), $myMemberId);
    }

    return Doctrine::getTable('MemberProfile')->getProfileListByMemberId($this->getId());
  }

  public function getProfile($profileName)
  {
    $profile = Doctrine::getTable('MemberProfile')->retrieveByMemberIdAndProfileName($this->getId(), $profileName);

    return $profile;
  }

  public function getConfig($configName)
  {
    $config = Doctrine::getTable('MemberConfig')->retrieveByNameAndMemberId($configName, $this->getId());

    if (!$config)
    {
      return null;
    }

    return $config->getValue();
  }

  public function setConfig($configName, $value)
  {
    $config = Doctrine::getTable('MemberConfig')->retrieveByNameAndMemberId($configName, $this->getId());
    if (!$config)
    {
      $config = new MemberConfig();
      $config->setMember($this);
      $config->setName($configName);
    }
    $config->setValue($value);
    $config->save();
  }

  public function getFriends($limit = null, $isRandom = false)
  {
    $q = Doctrine::getTable('Member')->createQuery()
        ->where('mr.member_id_to = ?', $this->getId())
        ->andWhere('mr.is_friend = ?', true)
        ->leftJoin('Member.MemberRelationship mr ON mr.member_id_from = Member.id');

    if (!is_null($limit))
    {
      $q->limit($limit);
    }

    if ($isRandom)
    {
      $expr = new Doctrine_Expression('RANDOM()');
      $q->orderBy($expr);
    }

    return $q->execute();
  }

  public function countFriends()
  {
    return $this->getFriends()->count();
  }

  public function getNameAndCount($format = '%s (%d)')
  {
    return sprintf($format, $this->getName(), $this->countFriends());
  }
  
  public function getJoinCommunities($limit = null, $isRandom = false)
  {
    return Doctrine::getTable('Community')->retrievesByMemberId($this->getId(), $limit, $isRandom);
  }

  public function getFriendPreTo(Doctrine_Query $q = null)
  {
    if (!$q)
    {
      $q = Doctrine::getTable('MemberRelationship')->createQuery();
    }
    $q->where('member_id_to = ?', $this->getId());
    $q->addWhere('is_friend_pre = ?', true);
    return $q->execute();
  }

  public function countFriendPreTo(Criteria $c = null)
  {
  /*
    if (!$c)
    {
      $c = new Criteria();
    }
    $c->add(MemberRelationshipPeer::IS_FRIEND_PRE, true);
    return $this->countMemberRelationshipsRelatedByMemberIdTo($c);
    */

    return array();
  }

  public function getFriendPreFrom(Criteria $c = null)
  {
  /*
    if (!$c)
    {
      $c = new Criteria();
    }
    $c->add(MemberRelationshipPeer::IS_FRIEND_PRE, true);
    return $this->getMemberRelationshipsRelatedByMemberIdFrom($c);
    */

    return array();
  }

  public function countFriendPreFrom(Criteria $c = null)
  {
  /*
    if (!$c)
    {
      $c = new Criteria();
    }
    $c->add(MemberRelationshipPeer::IS_FRIEND_PRE, true);
    return $this->countMemberRelationshipsRelatedByMemberIdFrom($c);
    */

    return 0;
  }

  public function getImage()
  {
    return Doctrine::getTable('MemberImage')->createQuery()
      ->where('member_id = ?', $this->getId())
      ->orderBy('is_primary DESC')
      ->fetchOne();
  }

  public function getImageFileName()
  {
    if ($this->getImage())
    {
      return $this->getImage()->getFile();
    }

    return false;
  }

  public function updateLastLoginTime()
  {
    $this->setConfig('lastLogin', time());
  }

  public function getLastLoginTime()
  {
    return $this->getConfig('lastLogin');
  }

  public function isOnBlackList()
  {
    $uid = $this->getConfig('mobile_uid');
    if ($uid)
    {
      return (bool)Doctrine::getTable('Blacklist')->retrieveByUid($uid);
    }

    return false;
  }

  public function getInvitingMembers()
  {
    return Doctrine::getTable('Member')->retrivesByInviteMemberId($this->getId());
  }

  public function getInviteMember()
  {
    return Doctrine::getTable('Member')->find($this->getInviteMemberId());
  }

  public function getEmailAddress($isPriorityMobile = null)
  {
    if (is_null($isPriorityMobile))
    {
      $isPriorityMobile = false;
      if (sfConfig::get('sf_app') == 'mobile_frontend')
      {
        $isPriorityMobile = true;
      }
    }

    $memberPcAddress     = $this->getConfig('pc_address');
    $memberMobileAddress = $this->getConfig('mobile_address');

    if ($memberMobileAddress && ($isPriorityMobile || !$memberPcAddress))
    {
      return $memberMobileAddress;
    }

    if ($memberPcAddress)
    {
      return $memberPcAddress;
    }

    return null;
  }

  public function getEmailAddresses()
  {
    $result = array();

    $memberPcAddress     = $this->getConfig('pc_address');
    $memberMobileAddress = $this->getConfig('mobile_address');

    if ($memberPcAddress)
    {
      $result[] = $memberPcAddress;
    }

    if ($memberMobileAddress)
    {
      $result[] = $memberMobileAddress;
    }

    return $result;
  }
}
