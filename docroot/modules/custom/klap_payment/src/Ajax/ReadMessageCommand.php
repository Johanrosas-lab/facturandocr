<?php

namespace Drupal\klap_payment\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Class ReadMessageCommand.
 */
class ReadMessageCommand implements CommandInterface {

  protected $id;
  /**
   * Constructs a ReadMessageCommand object.
   *
   * @param string $id
   *   The id for the id html.
   */
  public function __construct(string $id) {
    $this->id = $id;
  }

  /**
   * Render custom ajax command.
   *
   * @return ajax
   *   Command function.
   */
  public function render() {
    return [
      'command' => 'readMessage',
      'id' => $this->id,
    ];
  }

}
