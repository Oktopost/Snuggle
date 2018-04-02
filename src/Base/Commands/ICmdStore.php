<?php
namespace Snuggle\Base\Commands;


interface ICmdStore extends ICmdInsert, IRevCommand, IStoreConflict
{
	
}